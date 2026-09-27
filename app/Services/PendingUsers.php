<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Legacy unverified identities, kept without granting account access. */
class PendingUsers
{
    public const RELATIONS = ['comments', 'canal_user', 'saved_posts', 'favorites', 'messengers'];

    public function move(int $id, bool $technical = false): void
    {
        DB::transaction(function () use ($id, $technical) {
            $user = DB::table('users')->where('id', $id)->lockForUpdate()->first();
            if (! $user || (! $technical && $user->email_verified_at !== null)) {
                return;
            }
            $snapshot = ['user' => (array) $user];
            foreach (['model_has_roles', 'model_has_permissions'] as $table) {
                $query = DB::table($table)->where('model_type', User::class)->where('model_id', $id);
                $snapshot[$table] = $query->get()->map(fn ($row) => (array) $row)->all();
                $query->delete();
            }
            DB::table('pending_users')->insert([
                'id' => $id, 'email' => $user->email, 'kind' => $technical ? 'system' : 'legacy',
                'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'created_at' => $user->created_at, 'updated_at' => now(),
            ]);
            DB::table('comments')->where('user_id', $id)->whereNull('user_name')
                ->update(['user_name' => mb_substr(trim($user->first_name . ' ' . $user->last_name), 0, 100)]);
            foreach (self::RELATIONS as $table) {
                DB::table($table)->where('user_id', $id)->update(['user_id' => null, 'pending_user_id' => $id]);
            }
            DB::table('sessions')->where('user_id', $id)->delete();
            DB::table('personal_access_tokens')->where('tokenable_type', User::class)->where('tokenable_id', $id)->delete();
            DB::table('users')->where('id', $id)->delete();
        });
    }

    /** Called only after email confirmation or verified social sign-in. */
    public function restoreVerified(string $email, array $profile = []): ?User
    {
        return DB::transaction(function () use ($email, $profile) {
            $pending = DB::table('pending_users')->where('email', $email)->lockForUpdate()->first();
            if (! $pending) {
                return null;
            }
            $snapshot = json_decode($pending->snapshot, true, flags: JSON_THROW_ON_ERROR);
            $attributes = $snapshot['user'];
            if ($pending->kind === 'system' || $attributes['deleted_at'] || $attributes['disabled'] || ($attributes['status'] ?? 'active') !== 'active') {
                throw ValidationException::withMessages(['email' => 'Tento účet nie je aktívny. Kontaktujte administrátora webu.']);
            }
            $attributes = array_replace($attributes, array_intersect_key($profile, array_flip(['first_name', 'last_name', 'password'])));
            $attributes['email_verified_at'] = now();
            $attributes['remember_token'] = null;
            $attributes['password'] = $profile['password'] ?? \Illuminate\Support\Facades\Hash::make(Str::random(40));
            $attributes['api_token'] = Str::random(60);
            DB::table('users')->insert($attributes);
            foreach (self::RELATIONS as $table) {
                DB::table($table)->where('pending_user_id', $pending->id)->update(['user_id' => $pending->id, 'pending_user_id' => null]);
            }
            foreach (['model_has_roles', 'model_has_permissions'] as $table) {
                if ($snapshot[$table]) {
                    DB::table($table)->insert($snapshot[$table]);
                }
            }
            DB::table('pending_users')->where('id', $pending->id)->delete();
            $user = User::findOrFail($pending->id);
            if (! $user->roles()->exists()) {
                $user->assignRole('user');
            }
            app(UserActivation::class)->activate($user);

            return $user;
        });
    }
}

<?php

namespace App\Services;

use App\Models\PendingRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PendingLogin
{
    /** Password verification grants access only to confirmation instructions. */
    public function find(string $email, string $password): ?PendingRegistration
    {
        $legacy = DB::table('pending_users')->where('email', $email)->first();
        $profile = $legacy ? json_decode($legacy->snapshot, true, flags: JSON_THROW_ON_ERROR)['user'] : null;
        if ($legacy && ($legacy->kind !== 'legacy' || ! empty($profile['deleted_at'])
            || ! empty($profile['disabled']) || ($profile['status'] ?? 'active') !== 'active')) {
            return null;
        }

        $pending = PendingRegistration::whereEmail($email)->first();
        // A newer registration takes precedence over the archived password.
        $hash = $pending?->password ?? ($profile['password'] ?? null);
        if (! $hash || ! Hash::check($password, $hash)) {
            return null;
        }

        if (! $pending) {
            $pending = PendingRegistration::create([
                'email' => $legacy->email,
                'first_name' => $profile['first_name'],
                'last_name' => $profile['last_name'],
                'password' => $hash,
            ]);
        }

        // Valid links survive login attempts. Sending another is an explicit action.
        if (! $pending->expires_at?->isFuture()) {
            $pending->sendConfirmation();
        }

        return $pending;
    }
}

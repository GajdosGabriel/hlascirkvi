<?php

namespace App\Filters;

use App\Models\SystemLog;
use App\Models\User;
use Carbon\Carbon;

class SystemLogFilters extends Filters
{
    protected $filters = ['search', 'channel', 'level', 'status', 'recipient', 'user', 'from', 'to'];

    /** Hľadá v príjemcovi aj v popise — väčšinou sa hľadá e-mail. */
    public function search($value)
    {
        $pattern = $this->likePattern($value);

        $this->builder->where(fn ($query) => $query
            ->where('recipient', 'LIKE', $pattern)
            ->orWhere('message', 'LIKE', $pattern));
    }

    public function channel($value)
    {
        $this->builder->where('channel', $value);
    }

    public function level($value)
    {
        if (in_array($value, SystemLog::LEVELS, true)) {
            $this->builder->where('level', $value);
        }
    }

    public function status($value)
    {
        if (in_array($value, SystemLog::STATUSES, true)) {
            $this->builder->where('status', $value);
        }
    }

    /** Presná zhoda — odkaz z detailu používateľa, ide po indexe. */
    public function recipient($value)
    {
        $this->builder->where('recipient', $value);
    }

    /**
     * Všetko o jednom človeku. Odoslané maily nesú len adresu (notifikácia
     * príjemcu do udalosti nedáva), preto aj zhoda s jeho e-mailom.
     */
    public function user($value)
    {
        $email = User::withTrashed()->whereKey((int) $value)->value('email');

        $this->builder->where(fn ($query) => $query
            ->where('user_id', (int) $value)
            ->when($email, fn ($query) => $query->orWhere('recipient', $email)));
    }

    public function from($value)
    {
        if ($date = $this->date($value)) {
            $this->builder->where('created_at', '>=', $date->startOfDay());
        }
    }

    public function to($value)
    {
        if ($date = $this->date($value)) {
            $this->builder->where('created_at', '<=', $date->endOfDay());
        }
    }

    private function date($value): ?Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', (string) $value) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
}

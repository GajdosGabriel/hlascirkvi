<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.09.2018
 * Time: 21:51
 */

namespace App\Filters;

use App\Enums\ModelStatus;

class UserFilters extends Filters
{
    /** Hodnota výberu stavu pre zrušené (soft-deleted) účty — nie je to ModelStatus. */
    public const DELETED = 'deleted';

    /** Koľko dní je účet „nový“ a za koľko dní sa ráta „aktívny“ (dlaždice a filtre). */
    public const RECENT_DAYS = 30;

    /**
     * Stĺpce, podľa ktorých sa dá radiť klikom na hlavičku tabuľky:
     * hodnota parametra => stĺpce v ORDER BY. ?sort=email vzostupne,
     * ?sort=-email zostupne.
     */
    public const SORTS = [
        'id' => ['id'],
        'name' => ['last_name', 'first_name'],
        'email' => ['email'],
        'status' => ['status'],
        'created' => ['created_at'],
        'login' => ['last_login_at'],
        'via' => ['last_login_via'],
    ];

    protected $filters = ['search', 'status', 'fresh', 'active', 'never', 'unverified', 'via', 'sort'];

    /** Neznáma hodnota nerobí nič; o radení potom rozhodne latest() vo Filters::apply(). */
    public function sort($value)
    {
        $desc = str_starts_with((string) $value, '-');
        $columns = self::SORTS[ltrim((string) $value, '-')] ?? null;

        if (! $columns) {
            return $this->builder;
        }

        $direction = $desc ? 'desc' : 'asc';

        // Prázdne hodnoty (nikdy neprihlásený, neznámy spôsob) vždy na koniec.
        if (in_array($columns[0], ['last_login_at', 'last_login_via'], true)) {
            $this->builder->orderByRaw($columns[0].' IS NULL');
        }

        foreach ($columns as $column) {
            $this->builder->orderBy($column, $direction);
        }

        // Pri zhode (rovnaký stav, spôsob…) drží poradie stabilné medzi stránkami.
        return $columns === ['id'] ? $this->builder : $this->builder->orderBy('id', $direction);
    }

    public function fresh()
    {
        return $this->builder->where('created_at', '>=', now()->subDays(self::RECENT_DAYS));
    }

    public function active()
    {
        return $this->builder->where('last_login_at', '>=', now()->subDays(self::RECENT_DAYS));
    }

    public function never()
    {
        return $this->builder->whereNull('last_login_at');
    }

    public function unverified()
    {
        return $this->builder->whereNull('email_verified_at');
    }

    /** Spôsob posledného prihlásenia (password, google, facebook). */
    public function via($value)
    {
        return $this->builder->where('last_login_via', (string) $value);
    }

    public function search()
    {
        session()->flash('search', $this->request->search);

        // Zoskupené, inak by orWhere prebilo výber stavu.
        return $this->builder->where(function ($query) {
            $query->where('email', 'LIKE', $this->likePattern($this->request->search))
                ->orWhere('first_name', 'LIKE', $this->likePattern($this->request->search))
                ->orWhere('last_name', 'LIKE', $this->likePattern($this->request->search));
        });
    }

    public function status($value)
    {
        if ($value === self::DELETED) {
            return $this->builder->onlyTrashed();
        }

        $status = ModelStatus::tryFrom((string) $value);

        if ($status === ModelStatus::Blocked) {
            // Starší stĺpec `disabled` stále označuje blokáciu (viď User::banned()).
            return $this->builder->where(function ($query) {
                $query->where('status', ModelStatus::Blocked->value)->orWhere('disabled', true);
            });
        }

        return $status ? $this->builder->where('status', $status->value) : $this->builder;
    }
}

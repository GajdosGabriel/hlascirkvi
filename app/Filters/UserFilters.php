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

    protected $filters = ['search', 'status', 'sort'];

    /** Radenie podľa id: ?sort=id vzostupne, ?sort=-id zostupne. Iné hodnoty sa ignorujú. */
    public function sort($value)
    {
        return match ($value) {
            'id' => $this->builder->orderBy('id'),
            '-id' => $this->builder->orderByDesc('id'),
            default => $this->builder,
        };
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

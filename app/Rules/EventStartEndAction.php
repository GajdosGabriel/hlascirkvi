<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class EventStartEndAction implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Validacia pre admina 
        $startAt = new Carbon($value);
        $dateNow = Carbon::now();

        if (auth()->user()->hasRole('superadmin')) {
            return true;
        }

        return $startAt > $dateNow;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Dátum a čas musí byť vyšší ako je teraz.';
    }
}

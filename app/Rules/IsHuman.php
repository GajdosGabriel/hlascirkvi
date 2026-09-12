<?php

namespace App\Rules;

use App\Support\HumanCheck;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Tenká obálka nad App\Support\HumanCheck — samotné rozhodovanie je tam,
 * tu je len napojenie na validátor. Pravidlo sa vešia na pole s pečiatkou
 * (HumanCheck::STAMP), ale posudzuje celú požiadavku, lebo k nej patrí aj
 * pole-pasca.
 */
class IsHuman implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    protected array $data = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($problem = HumanCheck::problem($this->data)) {
            $fail($problem);
        }
    }
}

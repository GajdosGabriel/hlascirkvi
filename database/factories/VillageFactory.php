<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Tabuľka `villages` je číselník naplnený raz importom — jej `id` nemá
 * AUTO_INCREMENT, takže ho factory musí dodať sama.
 */
class VillageFactory extends Factory
{
    public function definition()
    {
        $name = $this->faker->unique()->city;

        return [
            'id' => $this->faker->unique()->numberBetween(1, 900000),
            'fullname' => $name,
            'shortname' => $name,
            'zip' => $this->faker->numerify('#####'),
            'district_id' => 1,
            'region_id' => 1,
        ];
    }
}

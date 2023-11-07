<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Spatie\Permission\Traits\HasRoles;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        
        return [

            'user_id' => User::role('gamer')->inRandomOrder()->first()->id,
            'dice_A' => $this->faker->numberBetween(1, 6),
            'dice_B' => $this->faker->numberBetween(1, 6),

        ];

    }

}
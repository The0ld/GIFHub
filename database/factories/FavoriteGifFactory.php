<?php

namespace Database\Factories;

use App\Models\FavoriteGif;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FavoriteGif>
 */
class FavoriteGifFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FavoriteGif::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'gif_id' => $this->faker->uuid,
            'alias' => $this->faker->word,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

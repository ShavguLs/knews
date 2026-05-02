<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'category' => fake()->word(),
            'author' => fake()->name(),
            'body' => fake()->paragraphs(3, true),
            'image_url' => null,
            'published_at' => now(),
            'status' => 'done',
            'is_hero' => false,
        ];
    }
}

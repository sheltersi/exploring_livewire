<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'category' => fake()->randomElement(['Technology', 'Lifestyle', 'Business', 'Health', 'Travel']),
            'featured_image' => fake()->imageUrl(800, 400, 'nature'),
            'status' => fake()->randomElement(['draft', 'published']),
            'meta_title' => $title,
            'meta_description' => fake()->sentence(),
            'published_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}

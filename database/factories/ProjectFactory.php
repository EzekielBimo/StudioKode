<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true) . ' Sanctuary';

        return [
            'title' => ucwords($title),
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(1),
            'description' => fake()->paragraphs(3, true),
            'client' => fake()->company() . ' Trust',
            'location' => fake()->city() . ', ' . fake()->country(),
            'year' => fake()->numberBetween(2020, 2024),
            'area_size' => fake()->numberBetween(150, 2500) . ' m²',
            'cover_image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=1600&q=80',
            'masterplan_image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
            'is_featured' => fake()->boolean(50),
            'published_at' => now()->subDays(fake()->numberBetween(1, 100)),
        ];
    }

    /**
     * Indicate that the project is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}

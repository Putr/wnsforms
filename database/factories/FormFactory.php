<?php

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Form>
 */
class FormFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hash' => $this->faker->unique()->md5,
            'name' => $this->faker->words(3, true) . ' Form',
            'allowed_domains' => null, // Allow all domains by default
            'notification_email' => $this->faker->safeEmail,
            'success_redirect' => $this->faker->url,
            'error_redirect' => $this->faker->url,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the form is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set specific allowed domains.
     */
    public function withAllowedDomains(array $domains): static
    {
        return $this->state(fn(array $attributes) => [
            'allowed_domains' => $domains,
        ]);
    }

    /**
     * Set no redirects.
     */
    public function withoutRedirects(): static
    {
        return $this->state(fn(array $attributes) => [
            'success_redirect' => null,
            'error_redirect' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'name' => $this->faker->unique()->word,
            'type' => $this->faker->randomElement(['text', 'email', 'phone', 'url']),
            'required' => $this->faker->boolean,
            'validation_rules' => null,
        ];
    }

    public function required(): static
    {
        return $this->state(fn(array $attributes) => [
            'required' => true,
        ]);
    }

    public function optional(): static
    {
        return $this->state(fn(array $attributes) => [
            'required' => false,
        ]);
    }

    public function email(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'email',
        ]);
    }

    public function text(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'text',
        ]);
    }

    public function honeypot(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'honeypot',
            'required' => false,
        ]);
    }
}

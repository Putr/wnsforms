<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormSubmission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'data' => [
                'name' => $this->faker->name,
                'email' => $this->faker->safeEmail,
                'message' => $this->faker->paragraph,
            ],
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
            'referrer' => $this->faker->url,
        ];
    }

    /**
     * Set custom form data.
     */
    public function withData(array $data): static
    {
        return $this->state(fn(array $attributes) => [
            'data' => $data,
        ]);
    }
}

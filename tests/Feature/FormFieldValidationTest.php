<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormFieldValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepts_submission_with_all_required_fields(): void
    {
        $form = Form::factory()->create();

        FormField::factory()
            ->for($form)
            ->required()
            ->text()
            ->create(['name' => 'name']);

        FormField::factory()
            ->for($form)
            ->required()
            ->email()
            ->create(['name' => 'email']);

        $response = $this->postJson("/api/post/{$form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, FormSubmission::count());
    }

    public function test_accepts_submission_with_optional_fields(): void
    {
        $form = Form::factory()->create();

        FormField::factory()
            ->for($form)
            ->optional()
            ->text()
            ->create(['name' => 'name']);

        FormField::factory()
            ->for($form)
            ->required()
            ->email()
            ->create(['name' => 'email']);

        $response = $this->postJson("/api/post/{$form->hash}", [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, FormSubmission::count());
    }

    public function test_rejects_submission_with_missing_required_field(): void
    {
        $form = Form::factory()->create();

        FormField::factory()
            ->for($form)
            ->required()
            ->text()
            ->create(['name' => 'name']);

        FormField::factory()
            ->for($form)
            ->required()
            ->email()
            ->create(['name' => 'email']);

        $response = $this->postJson("/api/post/{$form->hash}", [
            'name' => 'John Doe',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, FormSubmission::count());
    }

    public function test_rejects_submission_with_invalid_email(): void
    {
        $form = Form::factory()->create();

        FormField::factory()
            ->for($form)
            ->required()
            ->email()
            ->create(['name' => 'email']);

        $response = $this->postJson("/api/post/{$form->hash}", [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, FormSubmission::count());
    }

    public function test_rejects_submission_with_filled_honeypot(): void
    {
        $form = Form::factory()->create();

        FormField::factory()
            ->for($form)
            ->honeypot()
            ->create(['name' => 'website']);

        $response = $this->postJson("/api/post/{$form->hash}", [
            'website' => 'https://spam.com',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(0, FormSubmission::count());
    }

    public function test_accepts_submission_with_empty_honeypot(): void
    {
        $form = Form::factory()->create();

        FormField::factory()
            ->for($form)
            ->honeypot()
            ->create(['name' => 'website']);

        FormField::factory()
            ->for($form)
            ->required()
            ->email()
            ->create(['name' => 'email']);

        $response = $this->postJson("/api/post/{$form->hash}", [
            'website' => '',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, FormSubmission::count());
    }
}

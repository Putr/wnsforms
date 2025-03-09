<?php

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\RateLimiter;
use Mockery\MockeryInterface;

beforeEach(function () {
    // Create a test form
    $this->form = Form::factory()->create([
        'hash' => 'test-form-hash',
        'name' => 'Test Form',
        'allowed_domains' => ['example.com', 'test.com'],
        'is_active' => true,
        'error_redirect' => 'https://example.com/error',
    ]);
});

afterEach(function () {
    \Mockery::close();
});

test('can submit a form successfully', function () {
    $formData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'message' => 'This is a test message',
    ];

    $response = $this->withoutMiddleware()
        ->postJson("/post/{$this->form->hash}", $formData);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Form submission received']);

    // Check that the submission was saved to the database
    $this->assertDatabaseHas('form_submissions', [
        'form_id' => $this->form->id,
    ]);

    // Verify the data was stored correctly
    $submission = FormSubmission::latest()->first();
    $this->assertEquals($formData, $submission->data);
});

test('cannot submit to a non-existent form', function () {
    $response = $this->withoutMiddleware()
        ->postJson('/post/non-existent-hash', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertStatus(404)
        ->assertJson(['message' => 'Form not found']);
});

test('cannot submit to an inactive form', function () {
    // Update the form to be inactive
    $this->form->update(['is_active' => false]);

    $response = $this->withoutMiddleware()
        ->postJson("/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertStatus(404)
        ->assertJson(['message' => 'Form not found']);
});

test('domain restriction works correctly for JSON requests', function () {
    // Set the referer header to simulate a request from a specific domain
    $response = $this->withoutMiddleware()
        ->withHeaders([
            'referer' => 'https://example.com/contact',
            'Accept' => 'application/json',
        ])->postJson("/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertStatus(200);

    // Try with a non-allowed domain
    $response = $this->withoutMiddleware()
        ->withHeaders([
            'referer' => 'https://not-allowed.com/contact',
            'Accept' => 'application/json',
        ])->postJson("/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    // For JSON requests, we should still get a 403 status
    $response->assertStatus(403)
        ->assertJson(['message' => 'Domain not allowed']);
});

test('domain restriction works correctly for regular form submissions', function () {
    // Try with a non-allowed domain using a regular POST request
    $response = $this->withoutMiddleware()
        ->withHeaders([
            'referer' => 'https://not-allowed.com/contact',
        ])->post("/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    // For regular form submissions, we should get a redirect
    $response->assertRedirect($this->form->error_redirect)
        ->assertSessionHas('error', 'Domain not allowed');
});

test('honeypot field catches spam bots', function () {
    // Simulate a bot filling out the honeypot field
    $response = $this->withoutMiddleware()
        ->postJson("/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'website' => 'https://spam-site.com', // This is the honeypot field
        ]);

    // The API should still return a 200 response to not alert the bot
    $response->assertStatus(200);

    // But no submission should be saved
    $this->assertDatabaseCount('form_submissions', 0);
});

test('rate limiting prevents too many submissions', function () {
    // Mock the RateLimiter to simulate hitting the limit
    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(true);

    RateLimiter::shouldReceive('availableIn')
        ->once()
        ->andReturn(60);

    // We don't need to mock the hit method since we're simulating being rate limited

    $response = $this->withoutMiddleware()
        ->withHeaders([
            'Accept' => 'application/json',
        ])
        ->postJson("/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertStatus(429)
        ->assertJson([
            'message' => 'Too many submissions. Please try again later.',
        ]);
});

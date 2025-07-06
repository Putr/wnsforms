<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\RateLimiter;
use Mockery\MockeryInterface;

beforeEach(function () {
    // Create a test form with a fixed error_redirect
    $this->form = Form::factory()->create([
        'hash' => 'test-form-hash',
        'name' => 'Test Form',
        'allowed_domains' => ['example.com', 'test.com'],
        'is_active' => true,
        'error_redirect' => 'https://example.com/error',
    ]);

    // Create form fields
    FormField::create([
        'form_id' => $this->form->id,
        'name' => 'name',
        'type' => 'text',
        'label' => 'Name',
        'required' => true,
    ]);

    FormField::create([
        'form_id' => $this->form->id,
        'name' => 'email',
        'type' => 'email',
        'label' => 'Email',
        'required' => true,
    ]);

    FormField::create([
        'form_id' => $this->form->id,
        'name' => 'message',
        'type' => 'text',
        'label' => 'Message',
        'required' => true,
    ]);

    // Create honeypot field
    FormField::create([
        'form_id' => $this->form->id,
        'name' => 'website',
        'type' => 'honeypot'
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
        ->postJson("/api/post/{$this->form->hash}", $formData);

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
        ->postJson('/api/post/non-existent-hash', [
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
        ->postJson("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertStatus(404)
        ->assertJson(['message' => 'Form not found']);
});

test('domain restriction works correctly for JSON requests', function () {
    // Try with a non-allowed domain and JSON Accept header
    $response = $this->withoutMiddleware()
        ->withHeaders([
            'referer' => 'https://not-allowed.com/contact',
            'Accept' => 'application/json',
        ])
        ->postJson("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'Domain not allowed']);
});

test('domain restriction works correctly for regular form submissions', function () {
    $response = $this->withoutMiddleware()
        ->withHeaders([
            'referer' => 'https://not-allowed.com/contact',
        ])
        ->post("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message',
        ]);

    $response->assertRedirect('https://example.com/error')
        ->assertSessionHas('error', 'Domain not allowed');
});

test('honeypot field catches spam bots', function () {
    // Simulate a bot filling out the honeypot field

    $response = $this->withoutMiddleware()
        ->postJson("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message',
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
        ->postJson("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message',
        ]);

    $response->assertStatus(429)
        ->assertJson([
            'message' => 'Too many submissions. Please try again later.',
        ]);
});

test('spam keywords get flagged', function () {
    // Submit a form with spam keywords
    $response = $this->withoutMiddleware()
        ->postJson("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Buy cheap viagra online! Get your free pills now!',
        ]);

    // Should return 200 to not alert the bot
    $response->assertStatus(200);

    // But no submission should be saved
    $this->assertDatabaseCount('form_submissions', 0);
});

test('too many links get flagged as spam', function () {
    // Submit a form with more than 2 links
    $response = $this->withoutMiddleware()
        ->postJson("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Check out these links: https://link1.com https://link2.com https://link3.com',
        ]);

    // Should return 200 to not alert the bot
    $response->assertStatus(200);

    // But no submission should be saved
    $this->assertDatabaseCount('form_submissions', 0);
});

test('disposable email addresses get flagged as spam', function () {
    // Submit a form with a disposable email address
    $response = $this->withoutMiddleware()
        ->postJson("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'test@001310.xyz', // Known disposable email domain
            'message' => 'This is a legitimate message',
        ]);

    // Should return 200 to not alert the bot
    $response->assertStatus(200);

    // But no submission should be saved
    $this->assertDatabaseCount('form_submissions', 0);
});

test('submission is blocked for blacklisted email domain', function () {
    $formData = [
        'name' => 'John Doe',
        'email' => 'user@anonmails.de',
        'message' => 'Test message',
    ];
    $response = $this->withoutMiddleware()
        ->postJson("/api/post/{$this->form->hash}", $formData);
    $response->assertStatus(200);
    $this->assertDatabaseCount('form_submissions', 0);
});

test('submission is blocked for blacklisted exact email', function () {
    $formData = [
        'name' => 'John Doe',
        'email' => 'yawiviseya67@gmail.com',
        'message' => 'Test message',
    ];
    $response = $this->withoutMiddleware()
        ->postJson("/api/post/{$this->form->hash}", $formData);
    $response->assertStatus(200);
    $this->assertDatabaseCount('form_submissions', 0);
});

test('submission is allowed for non-blacklisted email/domain', function () {
    $formData = [
        'name' => 'John Doe',
        'email' => 'user@gmail.com',
        'message' => 'Test message',
    ];
    $response = $this->withoutMiddleware()
        ->postJson("/api/post/{$this->form->hash}", $formData);
    $response->assertStatus(200);
    $this->assertDatabaseHas('form_submissions', [
        'form_id' => $this->form->id,
    ]);
});

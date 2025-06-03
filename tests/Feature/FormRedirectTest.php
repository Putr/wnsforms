<?php

use App\Models\Form;
use Illuminate\Support\Facades\RateLimiter;
use Mockery\MockeryInterface;

beforeEach(function () {
    // Create a test form with redirect URLs
    $this->form = Form::factory()->create([
        'hash' => 'test-form-hash',
        'name' => 'Test Form',
        'allowed_domains' => ['example.com', 'test.com'],
        'success_redirect' => 'https://example.com/thank-you',
        'error_redirect' => 'https://example.com/error',
        'is_active' => true,
    ]);
});

afterEach(function () {
    \Mockery::close();
});

test('redirects to success URL after successful submission', function () {
    // Make a regular POST request (not JSON) to the web route
    $response = $this->withoutMiddleware()
        ->post("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message',
        ]);

    $response->assertRedirect($this->form->success_redirect)
        ->assertSessionHas('success', 'Form submitted successfully!');
});

test('redirects to error URL when domain is not allowed', function () {
    // Set the referer header to a non-allowed domain
    $response = $this->withoutMiddleware()
        ->withHeaders([
            'referer' => 'https://not-allowed.com/contact',
        ])->post("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertRedirect($this->form->error_redirect)
        ->assertSessionHas('error', 'Domain not allowed');
});

test('redirects to error URL when rate limited', function () {
    // Mock the RateLimiter to simulate hitting the limit
    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(true);

    RateLimiter::shouldReceive('availableIn')
        ->once()
        ->andReturn(60);

    $response = $this->withoutMiddleware()
        ->post("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertRedirect($this->form->error_redirect)
        ->assertSessionHas('error', 'Too many submissions. Please try again later.');
});

test('does not redirect for JSON requests', function () {
    $response = $this->withoutMiddleware()
        ->postJson("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message',
        ]);

    // Should return JSON response, not redirect
    $response->assertStatus(200)
        ->assertJson(['message' => 'Form submission received']);
});

test('falls back to JSON response when no success redirect is set', function () {
    // Update the form to remove the success redirect
    $this->form->update(['success_redirect' => null]);

    $response = $this->withoutMiddleware()
        ->post("/api/post/{$this->form->hash}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message',
        ]);

    // Should return JSON response, not redirect
    $response->assertStatus(200)
        ->assertJson(['message' => 'Form submission received']);
});

<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

class FormSubmissionController extends Controller
{
    public function submit(Request $request, string $hash)
    {
        // Check if this is a JSON request
        $wantsJson = $request->header('Accept') === 'application/json';

        // Check honeypot fields - if these are filled, it's likely a bot
        if ($request->filled('website') || $request->filled('phone_2')) {
            // Silently accept the submission but don't process it
            // This way bots won't know they've been detected
            return response()->json(['message' => 'Form submission received']);
        }

        // Rate limiting - 10 submissions per IP per hour
        $ipAddress = $request->ip();
        $rateLimiterKey = 'form_submissions:' . $ipAddress;

        if (RateLimiter::tooManyAttempts($rateLimiterKey, 10)) {
            $seconds = RateLimiter::availableIn($rateLimiterKey);

            // Find the form to check if there's an error redirect
            $form = Form::where('hash', $hash)->where('is_active', true)->first();

            if ($form && $form->error_redirect && !$wantsJson) {
                return redirect($form->error_redirect)
                    ->with('error', 'Too many submissions. Please try again later.');
            }

            return response()->json([
                'message' => 'Too many submissions. Please try again later.',
                'retry_after' => $seconds
            ], 429);
        }

        // Hit the rate limiter
        RateLimiter::hit($rateLimiterKey, 3600); // 1 hour expiry

        // Find the form by hash
        $form = Form::where('hash', $hash)->where('is_active', true)->first();

        if (!$form) {
            return response()->json(['message' => 'Form not found'], 404);
        }

        // Check if the domain is allowed
        $referrer = $request->header('referer');
        $referrerDomain = null;

        if ($referrer) {
            $parsedUrl = parse_url($referrer);
            if (isset($parsedUrl['host'])) {
                $referrerDomain = $parsedUrl['host'];
            }
        }

        if ($referrerDomain && !$form->isAllowedDomain($referrerDomain)) {
            if ($form->error_redirect && !$wantsJson) {
                return redirect($form->error_redirect)
                    ->with('error', 'Domain not allowed');
            }

            return response()->json(['message' => 'Domain not allowed'], 403);
        }

        // Remove honeypot fields from the data
        $data = $request->except(['_token', 'website', 'phone_2']);

        // Create a new submission
        $submission = new FormSubmission([
            'form_id' => $form->id,
            'data' => $data,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'referrer' => $referrer,
        ]);

        $submission->save();

        // Log the submission
        Log::info('Form submission received', [
            'form_id' => $form->id,
            'form_name' => $form->name,
            'data' => $data,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'referrer' => $referrer,
        ]);

        // Send to Slack if webhook URL is configured
        if ($form->slack_webhook_url) {
            $this->sendToSlack($form, $data);
        }

        // Send email notification if email is configured
        if ($form->notification_email) {
            $this->sendEmailNotification($form, $data, $submission);
        }

        // Check if we should redirect
        if ($wantsJson) {
            return response()->json(['message' => 'Form submission received']);
        } elseif ($form->success_redirect) {
            return redirect($form->success_redirect)
                ->with('success', 'Form submitted successfully!');
        } else {
            return response()->json(['message' => 'Form submission received']);
        }
    }

    /**
     * Send form submission data to Slack
     */
    private function sendToSlack(Form $form, array $data): void
    {
        try {
            // Format the data for Slack
            $blocks = [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => "New form submission: {$form->name}",
                        'emoji' => true
                    ]
                ],
                [
                    'type' => 'divider'
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Form Data:*"
                    ]
                ]
            ];

            // Add each field from the submission
            foreach ($data as $key => $value) {
                // Skip internal fields
                if (in_array($key, ['_token', 'website', 'phone_2'])) {
                    continue;
                }

                // Format the value (handle arrays, etc.)
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $blocks[] = [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*{$key}:* {$value}"
                    ]
                ];
            }

            // Add submission metadata
            $blocks[] = [
                'type' => 'divider'
            ];
            $blocks[] = [
                'type' => 'context',
                'elements' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "Submitted at: " . now()->format('Y-m-d H:i:s')
                    ]
                ]
            ];

            // Send to Slack
            Http::post($form->slack_webhook_url, [
                'blocks' => $blocks
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send to Slack', [
                'form_id' => $form->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email notification for form submission
     */
    private function sendEmailNotification(Form $form, array $data, FormSubmission $submission): void
    {
        try {
            // Format the data for email
            $formattedData = [];
            foreach ($data as $key => $value) {
                // Skip internal fields
                if (in_array($key, ['_token', 'website', 'phone_2'])) {
                    continue;
                }

                // Format the value (handle arrays, etc.)
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $formattedData[$key] = $value;
            }

            // Prepare email data
            $emailData = [
                'form' => $form,
                'data' => $formattedData,
                'submission' => $submission,
                'submittedAt' => now()->format('Y-m-d H:i:s'),
            ];

            // Send the email with both HTML and plain text versions
            Mail::send(
                ['html' => 'emails.form-submission', 'text' => 'emails.form-submission-text'],
                $emailData,
                function ($message) use ($form) {
                    $message->to($form->notification_email)
                        ->subject("New form submission: {$form->name}");
                }
            );

            Log::info('Email notification sent', [
                'form_id' => $form->id,
                'email' => $form->notification_email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'form_id' => $form->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

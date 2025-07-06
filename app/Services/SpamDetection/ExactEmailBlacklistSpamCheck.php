<?php

namespace App\Services\SpamDetection;

class ExactEmailBlacklistSpamCheck extends AbstractSpamCheck
{
    // Start with a simple hardcoded blacklist of exact email addresses
    private array $blacklistedEmails = [
        'yawiviseya67@gmail.com',
        'Caceresseguelnancy@gmail.com',
        'kmetzfwadia6f4@outlook.com',
        'dinanikolskaya99@gmail.com'

        // Add more as needed
    ];

    public function check(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && $this->isEmailField($key, $value)) {
                if (in_array(strtolower($value), $this->blacklistedEmails, true)) {
                    $this->message = 'This email address is blacklisted.';
                    return true;
                }
            }
        }
        return false;
    }

    private function isEmailField($key, $value): bool
    {
        // Check if the field name suggests it's an email, and if the value looks like an email
        return (stripos($key, 'email') !== false) && filter_var($value, FILTER_VALIDATE_EMAIL);
    }
}

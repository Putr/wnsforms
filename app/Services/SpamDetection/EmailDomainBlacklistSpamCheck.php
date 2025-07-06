<?php

namespace App\Services\SpamDetection;

class EmailDomainBlacklistSpamCheck extends AbstractSpamCheck
{
    // Start with a simple hardcoded blacklist
    private array $blacklistedDomains = [
        'anonmails.de',
        'carter.com',
        'cosmicbridge.site'

    ];

    public function check(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && $this->isEmailField($key, $value)) {
                $domain = strtolower(substr(strrchr($value, '@'), 1));
                if (in_array($domain, $this->blacklistedDomains, true)) {
                    $this->message = 'Email domain is blacklisted.';
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

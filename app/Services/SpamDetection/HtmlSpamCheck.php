<?php

namespace App\Services\SpamDetection;

class HtmlSpamCheck extends AbstractSpamCheck
{
    public function check(array $data): bool
    {
        foreach ($data as $value) {
            if (!is_string($value)) {
                continue;
            }

            if (strip_tags($value) !== $value) {
                $this->message = 'HTML content detected in submission';
                return true;
            }
        }

        return false;
    }
}

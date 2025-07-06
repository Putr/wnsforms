<?php

namespace App\Services\SpamDetection;

class UrlCountSpamCheck extends AbstractSpamCheck
{
    private int $maxUrls = 1;

    public function check(array $data): bool
    {
        $urlCount = 0;
        $content = implode(' ', $data);

        // Simple URL detection - you might want to use a more sophisticated regex
        $urlPattern = '/https?:\/\/[^\s]+/';
        preg_match_all($urlPattern, $content, $matches);

        $urlCount = count($matches[0] ?? []);

        if ($urlCount > $this->maxUrls) {
            $this->message = "Submission contains too many URLs ({$urlCount} found, maximum allowed: {$this->maxUrls})";
            return true;
        }

        return false;
    }
}

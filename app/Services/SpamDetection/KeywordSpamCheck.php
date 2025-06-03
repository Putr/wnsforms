<?php

namespace App\Services\SpamDetection;

class KeywordSpamCheck extends AbstractSpamCheck
{
    private array $spamKeywords = [
        'bitcoin',
        'crypto',
        'viagra',
        'cialis',
        'casino',
        'gambling',
        'lottery',
        'winner',
        'inheritance',
        'nigerian',
        'prince',
        'bank transfer',
        'wire transfer',
        'forex',
        'trading',
        'investment',
        'earn money',
        'make money fast',
        'work from home',
        'weight loss',
        'diet pills',
        'prescription',
        'pharmacy',
        'cheap',
        'free trial',
        'limited time',
        'act now',
        'click here',
        'subscribe',
        'unsubscribe',
        'congratulations',
        'you\'ve won',
        'claim your prize',
        'verify your account',
        'confirm your details',
        'suspicious activity',
        'account suspended',
        'password expired',
        'security check',
        'instagram',
        'tiktok',
        'followers',
        'sex',
        'porn',
        "SEO ",
        'backlinks',
        'forex',
        'WhatsApp',
        'Facebook',
        'Twitter',
        'LinkedIn',
        'Instagram',
        'TikTok',
        'YouTube',
        'escort'
    ];

    public function check(array $data): bool
    {
        $content = strtolower(implode(' ', $data));

        foreach ($this->spamKeywords as $keyword) {
            if (str_contains($content, strtolower($keyword))) {
                $this->message = "Submission contains spam keyword: {$keyword}";
                return true;
            }
        }

        return false;
    }
}

<?php

namespace App\Services\SpamDetection;

abstract class AbstractSpamCheck implements SpamCheckInterface
{
    protected string $message = '';

    public function getMessage(): string
    {
        return $this->message;
    }
}

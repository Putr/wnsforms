<?php

namespace App\Services\SpamDetection;

interface SpamCheckInterface
{
    public function check(array $data): bool;
    public function getMessage(): string;
}

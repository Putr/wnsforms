<?php

namespace App\Services\SpamDetection;

class SpamDetector
{
    private array $checks = [];

    public function addCheck(SpamCheckInterface $check): self
    {
        $this->checks[] = $check;
        return $this;
    }

    public function detect(array $data): ?string
    {
        foreach ($this->checks as $check) {
            if ($check->check($data)) {
                return $check->getMessage();
            }
        }

        return null;
    }
}

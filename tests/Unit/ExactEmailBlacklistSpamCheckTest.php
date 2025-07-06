<?php

use App\Services\SpamDetection\ExactEmailBlacklistSpamCheck;

test('blocks blacklisted exact emails', function () {
    $check = new ExactEmailBlacklistSpamCheck();
    $data = [
        'email' => 'yawiviseya67@gmail.com',
    ];
    expect($check->check($data))->toBeTrue();
    expect($check->getMessage())->toBe('This email address is blacklisted.');
});

test('allows non-blacklisted emails', function () {
    $check = new ExactEmailBlacklistSpamCheck();
    $data = [
        'email' => 'user@gmail.com',
    ];
    expect($check->check($data))->toBeFalse();
});

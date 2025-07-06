<?php

use App\Services\SpamDetection\EmailDomainBlacklistSpamCheck;

test('blocks blacklisted email domains', function () {
    $check = new EmailDomainBlacklistSpamCheck();
    $data = [
        'email' => 'user@anonmails.de',
    ];
    expect($check->check($data))->toBeTrue();
    expect($check->getMessage())->toBe('Email domain is blacklisted.');
});

test('allows non-blacklisted email domains', function () {
    $check = new EmailDomainBlacklistSpamCheck();
    $data = [
        'email' => 'user@gmail.com',
    ];
    expect($check->check($data))->toBeFalse();
});

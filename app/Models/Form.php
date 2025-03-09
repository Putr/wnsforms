<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'hash',
        'name',
        'allowed_domains',
        'notification_email',
        'success_redirect',
        'error_redirect',
        'slack_webhook_url',
        'is_active',
    ];

    protected $casts = [
        'allowed_domains' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            if (empty($form->hash)) {
                $form->hash = bin2hex(random_bytes(8));
            }
        });
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function isAllowedDomain(?string $domain): bool
    {
        if (empty($this->allowed_domains)) {
            return true;
        }

        return in_array($domain, $this->allowed_domains);
    }
}

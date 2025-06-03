<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'name',
        'type',
        'required',
        'validation_rules',
    ];

    protected $casts = [
        'required' => 'boolean',
        'validation_rules' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function getValidationRules(): array
    {
        $rules = [];

        // Add type-specific rules
        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                $rules[] = 'disposable_email';
                break;
            case 'phone':
                $rules[] = 'min:5|max:20';
                break;
            case 'url':
                $rules[] = 'url|min:5|max:2048';
                break;
            case 'honeypot':
                $rules[] = 'prohibited';
                break;
            case 'text':
            default:
                $rules[] = 'string';
                $rules[] = 'min:1';
                $rules[] = 'max:1000';
                break;
        }

        // If required is true, the field must exist and not be null
        if ($this->required) {
            $rules[] = 'required';
            $rules[] = 'filled';
        } else {
            // If required is false, the field can either not exist or be null
            $rules[] = 'nullable';
        }

        // Add any custom validation rules
        if ($this->validation_rules) {
            $rules = array_merge($rules, explode('|', $this->validation_rules));
        }

        return $rules;
    }
}

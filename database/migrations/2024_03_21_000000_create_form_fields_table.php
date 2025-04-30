<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['text', 'email', 'phone', 'url', 'honeypot'])->default('text');
            $table->boolean('required')->default(false);
            $table->json('validation_rules')->nullable(); // Custom validation rules
            $table->timestamps();

            $table->unique(['form_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('form_fields');
    }
};

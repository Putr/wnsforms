<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            // Only add the columns if they don't already exist
            if (!Schema::hasColumn('forms', 'success_redirect')) {
                $table->string('success_redirect')->nullable()->after('notification_email');
            }

            if (!Schema::hasColumn('forms', 'error_redirect')) {
                $table->string('error_redirect')->nullable()->after('success_redirect');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            // Only drop the columns if they exist
            if (Schema::hasColumn('forms', 'success_redirect')) {
                $table->dropColumn('success_redirect');
            }

            if (Schema::hasColumn('forms', 'error_redirect')) {
                $table->dropColumn('error_redirect');
            }
        });
    }
};

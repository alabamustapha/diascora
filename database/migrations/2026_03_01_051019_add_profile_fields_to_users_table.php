<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone_number')->nullable()->after('avatar_url');
            $table->boolean('whatsapp_enabled')->default(false)->after('phone_number');
            $table->boolean('telegram_enabled')->default(false)->after('whatsapp_enabled');
            $table->string('country_of_origin', 2)->nullable()->after('telegram_enabled');
            $table->string('country_of_residence', 2)->nullable()->after('country_of_origin');
            $table->json('payment_methods')->nullable()->after('country_of_residence');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone_number', 'whatsapp_enabled', 'telegram_enabled', 'country_of_origin', 'country_of_residence', 'payment_methods']);
        });
    }
};

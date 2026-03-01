<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_anonymous')->default(false);
            $table->char('from_currency', 3);
            $table->char('to_currency', 3);
            $table->decimal('from_amount', 12, 2);
            $table->decimal('to_amount', 12, 2);
            $table->decimal('official_rate_at_posting', 12, 6);
            $table->decimal('offered_rate', 12, 6);
            $table->string('payment_method_sending');
            $table->string('payment_method_receiving');
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'matched', 'closed'])->default('open');
            $table->unsignedBigInteger('accepted_interest_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_requests');
    }
};

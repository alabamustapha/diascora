<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_interests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exchange_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->string('payment_method_sending');
            $table->string('payment_method_receiving');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
            $table->unique(['exchange_request_id', 'user_id']);
        });

        Schema::table('exchange_requests', function (Blueprint $table): void {
            $table->foreign('accepted_interest_id')
                ->references('id')->on('exchange_interests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exchange_requests', function (Blueprint $table): void {
            $table->dropForeign(['accepted_interest_id']);
        });

        Schema::dropIfExists('exchange_interests');
    }
};

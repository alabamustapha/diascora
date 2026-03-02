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
        Schema::create('delivery_offers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('delivery_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
            $table->unique(['delivery_request_id', 'user_id']);
        });

        Schema::table('delivery_requests', function (Blueprint $table): void {
            $table->foreign('accepted_offer_id')
                ->references('id')->on('delivery_offers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_requests', function (Blueprint $table): void {
            $table->dropForeign(['accepted_offer_id']);
        });

        Schema::dropIfExists('delivery_offers');
    }
};

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
        Schema::create('delivery_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('destination_country', 2);
            $table->decimal('weight_kg', 8, 2);
            $table->decimal('payment_amount', 12, 2);
            $table->char('payment_currency', 3);
            $table->string('payment_method');
            $table->text('description');
            $table->string('item_image_path')->nullable();
            $table->enum('status', ['open', 'matched', 'closed'])->default('open');
            $table->unsignedBigInteger('accepted_offer_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_requests');
    }
};

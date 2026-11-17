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
        Schema::table('assistance_requests', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id', 255)->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->string('payment_proof_public_id', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assistance_requests', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_payment_intent_id',
                'card_last4',
                'payment_proof_public_id',
            ]);
        });
    }
};

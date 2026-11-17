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
        Schema::table('demarches', function (Blueprint $table) {
            $table->decimal('assistance_price', 10, 2)->default(50.00);
        });

        Schema::table('assistance_requests', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('currency', 10)->default('BRL');
            $table->string('payment_method', 30)->nullable();
            $table->string('payment_status', 30)->default('pending_payment');
            $table->string('payment_reference')->nullable();
            $table->string('payment_proof_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assistance_requests', function (Blueprint $table) {
            $table->dropColumn([
                'amount',
                'currency',
                'payment_method',
                'payment_status',
                'payment_reference',
                'payment_proof_url',
            ]);
        });

        Schema::table('demarches', function (Blueprint $table) {
            $table->dropColumn('assistance_price');
        });
    }
};


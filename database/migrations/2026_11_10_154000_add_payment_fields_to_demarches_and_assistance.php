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
            $table->decimal('assistance_price', 10, 2)->default(50.00)->after('cost');
        });

        Schema::table('assistance_requests', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->default(0.00)->after('details');
            $table->string('currency', 10)->default('BRL')->after('amount');
            $table->string('payment_method', 30)->nullable()->after('currency');
            $table->string('payment_status', 30)->default('pending_payment')->after('payment_method');
            $table->string('payment_reference')->nullable()->after('payment_status');
            $table->string('payment_proof_url')->nullable()->after('payment_reference');
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


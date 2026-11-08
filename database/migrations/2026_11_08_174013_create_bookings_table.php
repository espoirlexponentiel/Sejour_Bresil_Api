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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('stay_id')->nullable()->constrained('stays')->nullOnDelete();
            $table->string('stay_title');
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->unsignedInteger('travelers')->default(1);
            $table->string('travel_date');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('status')->default('en_attente'); // en_attente, confirmée, annulée
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

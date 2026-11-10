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
        Schema::create('aid_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type_badge')->default('Service public');
            $table->string('city'); // São Paulo (SP), Rio de Janeiro (RJ)...
            $table->string('city_slug')->default('sao-paulo');
            $table->json('services')->nullable();
            $table->string('address');
            $table->string('hours');
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aid_centers');
    }
};

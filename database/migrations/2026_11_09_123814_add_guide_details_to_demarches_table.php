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
            $table->string('subtitle')->nullable();
            $table->json('questions')->nullable();
            $table->json('checklist')->nullable();
            $table->json('steps')->nullable();
            $table->json('faqs')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demarches', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'questions', 'checklist', 'steps', 'faqs']);
        });
    }
};

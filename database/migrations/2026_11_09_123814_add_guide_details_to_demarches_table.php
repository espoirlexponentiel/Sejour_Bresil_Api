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
            $table->string('subtitle')->nullable()->after('title');
            $table->json('questions')->nullable()->after('official_source');
            $table->json('checklist')->nullable()->after('questions');
            $table->json('steps')->nullable()->after('checklist');
            $table->json('faqs')->nullable()->after('steps');
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

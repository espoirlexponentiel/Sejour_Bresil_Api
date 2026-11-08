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
        Schema::create('stays', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('destination');
            $table->string('region')->default('Brésil');
            $table->string('duration');
            $table->unsignedInteger('days')->default(7);
            $table->decimal('price', 10, 2);
            $table->decimal('rating', 3, 2)->default(4.90);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->text('image')->nullable();
            $table->string('category')->default('Découverte');
            $table->string('tag')->nullable();
            $table->boolean('featured')->default(false);
            $table->string('status')->default('active'); // active, draft, archived
            $table->text('summary');
            $table->json('highlights')->nullable();
            $table->json('included')->nullable();
            $table->json('dates')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stays');
    }
};

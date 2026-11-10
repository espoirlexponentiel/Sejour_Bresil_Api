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
        Schema::create('demarches', function (Blueprint $table) {
            $table->id();
            $table->string('step_number')->default('ÉTAPE 01'); // ÉTAPE 01, ÉTAPE 02...
            $table->string('priority')->default('Priorité 1'); // Priorité 1, Recommandé...
            $table->string('icon')->default('credit-card'); // credit-card, file-text, shield, building...
            $table->string('title'); // CPF (Numéro fiscal), RNM / RNE...
            $table->text('description');
            $table->string('delay')->nullable(); // Immédiat en bureau · 24 à 72h en ligne
            $table->string('cost')->nullable(); // 100% Gratuit en ligne / par e-mail · 7,00 R$ en agence
            $table->string('button_text')->default('Voir le guide officiel');
            $table->string('guide_url')->nullable();
            $table->text('official_source')->nullable(); // Receita Federal, Polícia Federal...
            $table->string('status')->default('active'); // active, draft
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demarches');
    }
};

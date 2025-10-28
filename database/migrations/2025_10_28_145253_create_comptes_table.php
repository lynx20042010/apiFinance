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
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained()->onDelete('cascade');
            $table->string('numeroCompte')->unique();
            $table->enum('type', ['cheque', 'courant', 'epargne', 'titre', 'devise']);
            $table->string('devise', 3)->default('XAF');
            $table->enum('statut', ['actif', 'inactif', 'bloque', 'ferme'])->default('actif');
            $table->decimal('solde', 15, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};

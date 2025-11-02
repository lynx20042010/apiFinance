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
            $table->string('id')->primary();
            $table->string('client_id');
            $table->string('numeroCompte')->unique();
            $table->enum('type', ['cheque', 'courant', 'epargne']);
            $table->string('devise', 3)->default('XOF');
            $table->enum('statut', ['actif', 'inactif', 'bloque', 'ferme'])->default('actif');
            $table->decimal('solde', 15, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->index(['client_id', 'statut']);
            $table->index(['type', 'statut']);
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

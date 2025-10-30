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
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('compte_id');
            $table->enum('type', ['depot', 'retrait', 'virement', 'transfert', 'interet', 'commission']);
            $table->decimal('montant', 15, 2);
            $table->string('devise', 3)->default('XOF');
            $table->text('description')->nullable();
            $table->string('compte_destination_id')->nullable();
            $table->enum('statut', ['en_attente', 'traitee', 'annulee', 'echouee'])->default('en_attente');
            $table->timestamp('date_execution')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');
            $table->foreign('compte_destination_id')->references('id')->on('comptes')->onDelete('set null');
            $table->index(['compte_id', 'statut', 'date_execution']);
            $table->index(['type', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

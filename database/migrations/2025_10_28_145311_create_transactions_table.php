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
            $table->uuid('id')->primary();
            $table->foreignUuid('compte_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['depot', 'retrait', 'virement', 'transfert', 'commission', 'interet']);
            $table->decimal('montant', 15, 2);
            $table->string('devise', 3)->default('XAF');
            $table->text('description')->nullable();
            $table->foreignUuid('compte_destination_id')->nullable()->constrained('comptes')->onDelete('set null');
            $table->enum('statut', ['en_attente', 'traitee', 'annulee', 'echouee'])->default('en_attente');
            $table->timestamp('date_execution')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
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

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
        Schema::create('clients', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id');
            $table->string('numeroCompte')->unique();
            $table->string('titulaire');
            $table->enum('type', ['particulier', 'entreprise']);
            $table->string('devise', 3)->default('XOF');
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

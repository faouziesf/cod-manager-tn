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
        Schema::table('orders', function (Blueprint $table) {
            // Ajouter les champs pour les importations externes
            if (!Schema::hasColumn('orders', 'external_id')) {
                $table->string('external_id')->nullable()->after('admin_id');
                $table->string('external_source')->nullable()->after('external_id');
                
                // Ajout d'un index pour les recherches par external_id
                $table->index('external_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Suppression de l'index
            $table->dropIndex(['external_id']);
            
            // Suppression des colonnes
            $table->dropColumn([
                'external_id',
                'external_source'
            ]);
        });
    }
};
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
        Schema::table('products', function (Blueprint $table) {
            // Ajouter les nouveaux champs si ils n'existent pas déjà
            if (!Schema::hasColumn('products', 'external_id')) {
                $table->string('external_id')->nullable()->after('admin_id');
            }
            
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('products', 'category')) {
                $table->string('category')->nullable()->after('active');
            }
            
            if (!Schema::hasColumn('products', 'dimensions')) {
                $table->json('dimensions')->nullable()->after('category');
            }
            
            if (!Schema::hasColumn('products', 'attributes')) {
                $table->json('attributes')->nullable()->after('dimensions');
            }
            
            // Ajout d'un index pour les recherches par external_id
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Suppression de l'index
            $table->dropIndex(['external_id']);
            
            // Suppression des colonnes
            $table->dropColumn([
                'external_id',
                'description',
                'sku',
                'category',
                'dimensions',
                'attributes'
            ]);
        });
    }
};
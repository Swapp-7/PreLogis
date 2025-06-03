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
        // Cette migration n'est plus nécessaire car nous n'utilisons plus de champ DESCRIPTION
        // Mais nous la gardons pour préserver l'historique des migrations
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ne rien faire
    }
};

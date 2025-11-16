<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            if (!Schema::hasColumn('availabilities', 'color')) {
                $table->string('color', 20)->default('verde')->after('date_of_availability');
            }
            // (opzionale ma utile) vincolo di unicità per data
            if (!Schema::hasColumn('availabilities', 'date_of_availability')) {
                // nulla: già esiste dalla tua migrazione iniziale
            }
            $table->unique('date_of_availability', 'availabilities_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {
            if (Schema::hasColumn('availabilities', 'color')) {
                $table->dropColumn('color');
            }
            // rimuovi l’unico se lo hai creato sopra
            $table->dropUnique('availabilities_date_unique');
        });
    }
};

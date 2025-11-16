<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('race_temps', function (Blueprint $table) {
            if (!Schema::hasColumn('race_temps', 'preventivo_da_aggiungere')) {
                // boolean con default false: 0 = No, 1 = Sì
                $table->boolean('preventivo_da_aggiungere')->default(false)->after('ente_fatturazione');
            }
        });
    }

    public function down(): void
    {
        Schema::table('race_temps', function (Blueprint $table) {
            if (Schema::hasColumn('race_temps', 'preventivo_da_aggiungere')) {
                $table->dropColumn('preventivo_da_aggiungere');
            }
        });
    }
};

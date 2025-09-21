<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('race_temps', function (Blueprint $table) {
            if (!Schema::hasColumn('race_temps', 'ente_fatturazione')) {
                $table->string('ente_fatturazione')->nullable()->after('place');
            }
            if (!Schema::hasColumn('race_temps', 'date_start')) {
                $table->date('date_start')->nullable()->after('date_of_race');
            }
            if (!Schema::hasColumn('race_temps', 'date_end')) {
                $table->date('date_end')->nullable()->after('date_start');
            }
            if (!Schema::hasColumn('race_temps', 'programma_allegato')) {
                $table->string('programma_allegato')->nullable()->after('date_end');
            }
            if (!Schema::hasColumn('race_temps', 'note')) {
                $table->text('note')->nullable()->after('programma_allegato');
            }
            if (!Schema::hasColumn('race_temps', 'organizer_email')) {
                $table->string('organizer_email')->nullable()->after('ente_fatturazione');
            }
        });
    }

    public function down(): void
    {
        Schema::table('race_temps', function (Blueprint $table) {
            $cols = ['ente_fatturazione', 'date_start', 'date_end', 'programma_allegato', 'note', 'organizer_email'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('race_temps', $c))
                    $table->dropColumn($c);
            }
        });
    }
};

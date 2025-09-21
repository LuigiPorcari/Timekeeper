<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            if (!Schema::hasColumn('races', 'ente_fatturazione')) {
                $table->string('ente_fatturazione')->nullable()->after('place');
            }
            if (!Schema::hasColumn('races', 'date_end')) {
                $table->date('date_end')->nullable()->after('date_of_race');
            }
            if (!Schema::hasColumn('races', 'programma_allegato')) {
                $table->string('programma_allegato')->nullable()->after('date_end');
            }
            if (!Schema::hasColumn('races', 'note')) {
                $table->text('note')->nullable()->after('programma_allegato');
            }
            if (!Schema::hasColumn('races', 'organizer_email')) {
                $table->string('organizer_email')->nullable()->after('ente_fatturazione');
            }
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $cols = ['ente_fatturazione', 'date_end', 'programma_allegato', 'note', 'organizer_email'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('races', $c))
                    $table->dropColumn($c);
            }
        });
    }
};

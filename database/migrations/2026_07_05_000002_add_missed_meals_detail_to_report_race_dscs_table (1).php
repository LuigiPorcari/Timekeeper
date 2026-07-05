<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ReportRaceDsc;

return new class extends Migration
{
    private function tableName(): string
    {
        $modelTable = (new ReportRaceDsc())->getTable();

        if (Schema::hasTable($modelTable)) {
            return $modelTable;
        }

        $possibleTables = [
            'report_race_dsc',
            'report_race_dscs',
            'report_dsc_race',
            'report_dsc_races',
        ];

        foreach ($possibleTables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return $modelTable;
    }

    public function up(): void
    {
        $tableName = $this->tableName();

        if (!Schema::hasColumn($tableName, 'missed_meals_detail')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->json('missed_meals_detail')->nullable()->after('missed_meals');
            });
        }
    }

    public function down(): void
    {
        $tableName = $this->tableName();

        if (Schema::hasColumn($tableName, 'missed_meals_detail')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('missed_meals_detail');
            });
        }
    }
};

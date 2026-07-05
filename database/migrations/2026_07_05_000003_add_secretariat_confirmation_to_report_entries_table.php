<?php

use App\Models\ReportEntry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tableName(): string
    {
        return (new ReportEntry())->getTable();
    }

    public function up(): void
    {
        $tableName = $this->tableName();

        if (!Schema::hasColumn($tableName, 'secretariat_confirmed')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->boolean('secretariat_confirmed')->default(false)->after('confirmed');
            });
        }

        if (!Schema::hasColumn($tableName, 'secretariat_confirmed_at')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->timestamp('secretariat_confirmed_at')->nullable()->after('secretariat_confirmed');
            });
        }
    }

    public function down(): void
    {
        $tableName = $this->tableName();

        if (Schema::hasColumn($tableName, 'secretariat_confirmed_at')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('secretariat_confirmed_at');
            });
        }

        if (Schema::hasColumn($tableName, 'secretariat_confirmed')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('secretariat_confirmed');
            });
        }
    }
};

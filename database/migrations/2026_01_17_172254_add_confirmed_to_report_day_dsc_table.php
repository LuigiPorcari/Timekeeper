<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_day_dsc', function (Blueprint $table) {
            if (!Schema::hasColumn('report_day_dsc', 'confirmed')) {
                $table->boolean('confirmed')->default(false)->after('apparecchiature');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_day_dsc', function (Blueprint $table) {
            if (Schema::hasColumn('report_day_dsc', 'confirmed')) {
                $table->dropColumn('confirmed');
            }
        });
    }
};

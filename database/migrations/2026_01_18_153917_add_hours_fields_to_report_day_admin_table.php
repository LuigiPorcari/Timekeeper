<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_day_admin', function (Blueprint $table) {
            $table->decimal('hours_special_service', 5, 2)->nullable()->after('work_date');
            $table->decimal('hours_ordinary_service', 5, 2)->nullable()->after('hours_special_service');
        });
    }

    public function down(): void
    {
        Schema::table('report_day_admin', function (Blueprint $table) {
            $table->dropColumn(['hours_special_service', 'hours_ordinary_service']);
        });
    }
};


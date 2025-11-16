<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            if (!Schema::hasColumn('records', 'apparecchiature')) {
                $table->json('apparecchiature')->nullable()->after('transport_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            if (Schema::hasColumn('records', 'apparecchiature')) {
                $table->dropColumn('apparecchiature');
            }
        });
    }
};

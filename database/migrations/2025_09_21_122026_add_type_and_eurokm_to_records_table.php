<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            if (!Schema::hasColumn('records', 'type')) {
                $table->string('type', 2)->nullable()->after('race_id'); // FC/CM/CP
            }
            if (!Schema::hasColumn('records', 'euroKM')) {
                $table->decimal('euroKM', 8, 2)->nullable()->after('type'); // es. 0.35 -> 0,35
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            if (Schema::hasColumn('records', 'euroKM')) {
                $table->dropColumn('euroKM');
            }
            if (Schema::hasColumn('records', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('race_temps', function (Blueprint $table) {
            if (!Schema::hasColumn('race_temps', 'type')) {
                $table->string('type')->nullable()->after('name');
            }
            // eventuale vecchio specialization_of_race lo puoi lasciare, ma non lo useremo più
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_temps', function (Blueprint $table) {
            $cols = ['type'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('race_temps', $c)) $table->dropColumn($c);
            }
        });
    }
};

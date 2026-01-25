<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_admin_race_settings', function (Blueprint $table) {
            $table->decimal('van_cost', 10, 2)->nullable()->after('coeff_km'); // Importo furgone (gara)
            $table->decimal('contributo_organizzativo', 10, 2)->nullable()->after('van_cost'); // gara
            $table->text('apparecchiature_note')->nullable()->after('contributo_organizzativo'); // testo note apparecchiature
            $table->decimal('spese_varie_gara', 10, 2)->nullable()->after('apparecchiature_note'); // gara
        });
    }

    public function down(): void
    {
        Schema::table('report_admin_race_settings', function (Blueprint $table) {
            $table->dropColumn([
                'van_cost',
                'contributo_organizzativo',
                'apparecchiature_note',
                'spese_varie_gara',
            ]);
        });
    }
};


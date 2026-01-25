<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_admin_race_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();

            // Uno per gara
            $table->decimal('coeff_km', 10, 4)->nullable();

            $table->timestamps();

            $table->unique(['race_id'], 'uniq_admin_settings_race');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_admin_race_settings');
    }
};

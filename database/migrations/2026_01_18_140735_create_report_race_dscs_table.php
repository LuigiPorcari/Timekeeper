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
        Schema::create('report_race_dsc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();

            // chi ha compilato (DSC)
            $table->foreignId('user_id')->constrained('users');

            // campi “una volta per gara”
            $table->boolean('van_needed')->default(false);
            $table->unsignedInteger('missed_meals')->default(0);
            $table->json('apparecchiature')->nullable();

            $table->boolean('confirmed')->default(false);
            $table->timestamps();

            $table->unique('race_id'); // una sola riga per gara
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_race_dscs');
    }
};

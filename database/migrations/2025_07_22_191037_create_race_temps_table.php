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
        Schema::create('race_temps', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('name');
            $table->string('place');
            $table->date('date_of_race');
            $table->json('specialization_of_race')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_temps');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_day_dsc', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->date('work_date');

            $table->time('morning_start')->nullable();
            $table->time('morning_end')->nullable();
            $table->time('afternoon_start')->nullable();
            $table->time('afternoon_end')->nullable();

            $table->boolean('van_needed')->default(false);
            $table->unsignedInteger('missed_meals')->default(0);
            $table->json('apparecchiature')->nullable();

            $table->timestamps();

            $table->unique(['race_id', 'user_id', 'work_date'], 'uniq_dsc_day_race_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_day_dsc');
    }
};

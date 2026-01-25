<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_day_admin', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->date('work_date');

            $table->decimal('van_cost', 10, 2)->nullable();
            $table->decimal('hours_specialist', 10, 2)->nullable();
            $table->decimal('hours_ordinary', 10, 2)->nullable();

            $table->timestamps();

            $table->unique(['race_id', 'user_id', 'work_date'], 'uniq_admin_day_race_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_day_admin');
    }
};

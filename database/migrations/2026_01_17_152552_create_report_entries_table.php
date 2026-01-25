<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // CRONO (una volta per gara)
            $table->decimal('km', 10, 2)->nullable();
            $table->decimal('pedaggi', 10, 2)->nullable();
            $table->decimal('vitto', 10, 2)->nullable();
            $table->decimal('alloggio', 10, 2)->nullable();
            $table->decimal('spese_varie', 10, 2)->nullable();
            $table->text('note')->nullable();

            $table->boolean('confirmed')->default(false);

            $table->timestamps();

            $table->unique(['race_id', 'user_id'], 'uniq_report_entry_race_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_entries');
    }
};

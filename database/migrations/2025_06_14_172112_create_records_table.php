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
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->integer('daily_service')->nullable();
            $table->integer('special_service')->nullable();
            $table->string('rate_documented')->nullable();
            $table->double('km_documented')->nullable();
            $table->double('amount_documented')->nullable();
            $table->double('travel_ticket_documented')->nullable();
            $table->double('food_documented')->nullable();
            $table->double('accommodation_documented')->nullable();
            $table->double('various_documented')->nullable();
            $table->double('food_not_documented')->nullable();
            $table->double('daily_allowances_not_documented')->nullable();
            $table->double('special_daily_allowances_not_documented')->nullable();
            $table->double('total')->nullable();
            $table->text('description')->nullable(); // permette molti caratteri
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('race_id');
            $table->foreign('race_id')->references('id')->on('races');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};

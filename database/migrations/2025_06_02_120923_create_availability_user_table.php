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
        Schema::create('availability_user', function (Blueprint $table) {
            $table->id();
            //FK Availabilities
            $table->unsignedBigInteger('availability_id');
            $table->foreign('availability_id')->references('id')->on('availabilities');
            //FK User
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_user');
    }
};

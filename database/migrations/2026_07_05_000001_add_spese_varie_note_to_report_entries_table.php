<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            $table->text('spese_varie_note')->nullable()->after('spese_varie');
        });
    }

    public function down(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            $table->dropColumn('spese_varie_note');
        });
    }
};

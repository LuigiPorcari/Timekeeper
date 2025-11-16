<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            if (!Schema::hasColumn('records', 'transport_mode')) {
                $table->enum('transport_mode', ['trasportato', 'km'])
                    ->default('km')
                    ->after('euroKM');
            }
        });
    }

    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            if (Schema::hasColumn('records', 'transport_mode')) {
                $table->dropColumn('transport_mode');
            }
        });
    }
};

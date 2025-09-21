<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            if (!Schema::hasColumn('races', 'type')) {
                $table->string('type')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            if (Schema::hasColumn('races', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};

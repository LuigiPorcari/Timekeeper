<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('availability_user', function (Blueprint $table) {
            if (!Schema::hasColumn('availability_user', 'morning')) {
                $table->boolean('morning')->default(false)->after('availability_id');
            }
            if (!Schema::hasColumn('availability_user', 'afternoon')) {
                $table->boolean('afternoon')->default(false)->after('morning');
            }
            if (!Schema::hasColumn('availability_user', 'trasferta')) {
                $table->boolean('trasferta')->default(false)->after('afternoon');
            }
            if (!Schema::hasColumn('availability_user', 'reperibilita')) {
                $table->boolean('reperibilita')->default(false)->after('trasferta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('availability_user', function (Blueprint $table) {
            $cols = ['morning', 'afternoon', 'trasferta', 'reperibilita'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('availability_user', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};

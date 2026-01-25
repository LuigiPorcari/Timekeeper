<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Se esistono, li eliminiamo (dato che non ti interessano i dati)
        Schema::dropIfExists('record_attachments');
        Schema::dropIfExists('records');
    }

    public function down(): void
    {
        // Down non ricrea i vecchi records per scelta (evitiamo caos).
        // Se ti serve, si può implementare, ma non conviene.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('promo_codes', 'code')) {
            Schema::table('promo_codes', function (Blueprint $table) {
                // drop unique index if exists
                try {
                    $table->dropUnique(['code']);
                } catch (\Throwable $e) {
                    // ignore
                }

                $table->dropColumn('code');
            });
        }
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->string('code')->unique()->nullable()->after('qr_code');
        });
    }
};

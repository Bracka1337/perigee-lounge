<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->string('qr_code', 100)->unique()->nullable()->after('code');
        });

        // Backfill existing promo codes with a unique QR code value
        $promoCodes = DB::table('promo_codes')->select('id', 'code')->get();
        foreach ($promoCodes as $p) {
            $value = (string) Str::uuid();
            DB::table('promo_codes')->where('id', $p->id)->update(['qr_code' => $value]);
        }
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropUnique(['qr_code']);
            $table->dropColumn('qr_code');
        });
    }
};

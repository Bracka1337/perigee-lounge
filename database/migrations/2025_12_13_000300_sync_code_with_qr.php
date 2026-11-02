<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For existing records, set `code` to `qr_code` when present
        if (Schema::hasColumn('promo_codes', 'qr_code')) {
            $promoCodes = DB::table('promo_codes')->select('id', 'qr_code')->get();
            foreach ($promoCodes as $p) {
                if ($p->qr_code !== null && $p->qr_code !== '') {
                    DB::table('promo_codes')->where('id', $p->id)->update(['code' => $p->qr_code]);
                }
            }
        }
    }

    public function down(): void
    {
        // no-op
    }
};

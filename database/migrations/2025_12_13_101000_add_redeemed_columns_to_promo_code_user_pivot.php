<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promo_code_user')) {
            Schema::table('promo_code_user', function (Blueprint $table) {
                if (! Schema::hasColumn('promo_code_user', 'is_redeemed')) {
                    $table->boolean('is_redeemed')->default(false)->after('is_used');
                }
                if (! Schema::hasColumn('promo_code_user', 'redeemed_at')) {
                    $table->timestamp('redeemed_at')->nullable()->after('claimed_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('promo_code_user')) {
            Schema::table('promo_code_user', function (Blueprint $table) {
                if (Schema::hasColumn('promo_code_user', 'is_redeemed')) {
                    $table->dropColumn('is_redeemed');
                }
                if (Schema::hasColumn('promo_code_user', 'redeemed_at')) {
                    $table->dropColumn('redeemed_at');
                }
            });
        }
    }
};

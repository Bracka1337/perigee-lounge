<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('loyalty_code')->nullable()->unique()->after('role');
            $table->decimal('loyalty_balance', 10, 2)->default(0)->after('loyalty_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['loyalty_code', 'loyalty_balance']);
        });
    }
};

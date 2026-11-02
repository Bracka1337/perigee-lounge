<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('newsletter_agreed')->default(false)->after('terms_agreed_at');
            $table->timestamp('newsletter_agreed_at')->nullable()->after('newsletter_agreed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('newsletter_agreed');
            $table->dropColumn('newsletter_agreed_at');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rooms of the default layout that guests can book as a whole.
     */
    private const BOOKABLE = ['VIP', 'Game room', 'Cinema room', 'Karaoke room'];

    /**
     * A reservation can hold a whole room. It still names one table of that room (the lead
     * table), so arrival, orders and notifications work as for a table booking, while the
     * booking holds every table in the room for the day.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('table_id')->constrained()->restrictOnDelete();
        });

        DB::table('rooms')->whereIn('name->en', self::BOOKABLE)->update(['is_bookable_whole' => true]);
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
        });
    }
};

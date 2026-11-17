<?php

use App\Actions\Lounge\ApplyDefaultLayout;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The light mood of the default rooms that are not "warm".
     */
    private const MOODS = [
        'Cinema room' => 'cinema',
        'Karaoke room' => 'neon',
        'Game room' => 'neon',
        'Bar' => 'bright',
        'Upstairs bar' => 'bright',
    ];

    /**
     * Rooms get a light mood (warm, neon, cinema or bright). Existing lounges get the default
     * moods and lights on their default floors, unless an admin already placed lights there.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('mood', 20)->default('warm')->after('color');
        });

        if (! DB::table('tables')->exists()) {
            return; // a fresh database gets everything from the seeders
        }

        foreach (self::MOODS as $name => $mood) {
            DB::table('rooms')->where('name->en', $name)->where('mood', 'warm')->update(['mood' => $mood]);
        }

        app(ApplyDefaultLayout::class)->addLights();
    }

    public function down(): void
    {
        DB::table('fixtures')->whereIn('type', ['pendant_light', 'floor_lamp', 'neon_strip', 'led_strip'])->delete();

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('mood');
        });
    }
};

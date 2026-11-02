<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->json('name_json')->nullable()->after('name');
            $table->json('description_json')->nullable()->after('description');
        });

        $default = config('site.default_locale', 'ru');

        $items = DB::table('menu_items')->get();
        foreach ($items as $item) {
            DB::table('menu_items')
                ->where('id', $item->id)
                ->update([
                    'name_json' => json_encode([$default => $item->name]),
                    'description_json' => $item->description
                        ? json_encode([$default => $item->description])
                        : null,
                ]);
        }

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
            $table->renameColumn('name_json', 'name');
            $table->renameColumn('description_json', 'description');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Doors and fixtures belong to a floor and are placed in metres, like rooms and tables.
     */
    public function up(): void
    {
        // A door sits on a wall line. "h" runs along x (a top or bottom wall), "v" along y.
        Schema::create('doors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20)->default('single');
            $table->char('orientation', 1);
            $table->decimal('x', 6, 2);
            $table->decimal('y', 6, 2);
            $table->decimal('width', 4, 2)->default(0.9);
            $table->timestamps();
        });

        // Everything else that stands in a room: screens, game stations, bar counters, plants, lights.
        // A bar counter is a chain of straight segments, stored as its corner points.
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->decimal('x', 6, 2);
            $table->decimal('y', 6, 2);
            $table->unsignedSmallInteger('rotation')->default(0);
            $table->decimal('width', 5, 2);
            $table->decimal('depth', 5, 2);
            $table->json('points')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
        Schema::dropIfExists('doors');
    }
};

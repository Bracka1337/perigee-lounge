<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('capacity')->default(2);

            $table->decimal('pos_x', 5, 2)->default(10);
            $table->decimal('pos_y', 5, 2)->default(10);

            $table->unsignedInteger('width')->default(80);
            $table->unsignedInteger('height')->default(80);
            $table->integer('rotation')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};

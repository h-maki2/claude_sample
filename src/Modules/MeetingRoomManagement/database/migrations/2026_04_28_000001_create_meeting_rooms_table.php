<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_rooms', function (Blueprint $table) {
            $table->string('meeting_room_id')->primary();
            $table->string('name', 50);
            $table->unsignedTinyInteger('capacity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_rooms');
    }
};

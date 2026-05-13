<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_room_equipments', function (Blueprint $table) {
            $table->string('meeting_room_id');
            $table->unsignedTinyInteger('equipment');
            $table->primary(['meeting_room_id', 'equipment']);
            $table->foreign('meeting_room_id')
                ->references('meeting_room_id')
                ->on('meeting_rooms')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_room_equipments');
    }
};

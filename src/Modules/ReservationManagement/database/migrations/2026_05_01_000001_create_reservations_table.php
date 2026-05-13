<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->string('reservation_id')->primary();
            $table->string('meeting_room_id');
            $table->string('name', 50);
            $table->string('contact_person_name', 30);
            $table->string('contact_email');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->unsignedTinyInteger('status');
            $table->timestamps();

            $table->foreign('meeting_room_id')
                ->references('meeting_room_id')
                ->on('meeting_rooms');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

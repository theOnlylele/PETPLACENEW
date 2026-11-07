<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pet_owners', function (Blueprint $table) {
            $table->date('pet_birthdate')->nullable();
            $table->string('pet_photo_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pet_owners', function (Blueprint $table) {
            $table->dropColumn(['pet_birthdate', 'pet_photo_path']);
        });
    }
};

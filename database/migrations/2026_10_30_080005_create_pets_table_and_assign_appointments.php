<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_owner_id')->constrained('pet_owners')->cascadeOnDelete();
            $table->string('pet_name')->nullable();
            $table->string('pet_type')->nullable();
            $table->date('pet_birthdate')->nullable();
            $table->string('pet_photo_path')->nullable();
            $table->timestamps();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('pet_id')->nullable()->constrained('pets')->nullOnDelete();
        });

        foreach (DB::table('pet_owners')->orderBy('id')->get() as $owner) {
            $petId = DB::table('pets')->insertGetId([
                'pet_owner_id' => $owner->id,
                'pet_name' => $owner->pet_name,
                'pet_type' => $owner->pet_type,
                'pet_birthdate' => $owner->pet_birthdate,
                'pet_photo_path' => $owner->pet_photo_path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('appointments')
                ->where('pet_owner_id', $owner->id)
                ->whereNull('pet_id')
                ->update(['pet_id' => $petId]);
        }
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['pet_id']);
            $table->dropColumn('pet_id');
        });

        Schema::dropIfExists('pets');
    }
};

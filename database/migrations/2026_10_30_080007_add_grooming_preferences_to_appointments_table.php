<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->json('grooming_addons')->nullable();
            $table->string('haircut_style')->nullable();
            $table->string('shampoo_type')->nullable();
            $table->boolean('fur_matted')->nullable();
            $table->string('pet_size')->nullable();
            $table->string('coat_type')->nullable();
            $table->string('temperament')->nullable();
            $table->text('allergies_or_skin_problems')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'grooming_addons',
                'haircut_style',
                'shampoo_type',
                'fur_matted',
                'pet_size',
                'coat_type',
                'temperament',
                'allergies_or_skin_problems',
            ]);
        });
    }
};

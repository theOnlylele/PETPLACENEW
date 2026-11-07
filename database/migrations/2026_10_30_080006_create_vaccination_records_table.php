<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->string('vaccine_key');
            $table->date('date_given');
            $table->date('next_due_date')->nullable();
            $table->string('veterinarian_name')->nullable();
            $table->string('dose_number')->nullable();
            $table->string('brand')->nullable();
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['pet_id', 'vaccine_key', 'date_given']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccination_records');
    }
};

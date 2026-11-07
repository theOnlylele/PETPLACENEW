<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->string('breed')->nullable();
            $table->decimal('weight', 7, 2)->nullable();
            $table->text('allergies')->nullable();
        });

        Schema::table('vets', function (Blueprint $table) {
            $table->json('working_days')->nullable();
            $table->time('working_hours_start')->nullable();
            $table->time('working_hours_end')->nullable();
            $table->json('days_off')->nullable();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->text('decision_reason')->nullable();
        });

        Schema::table('vaccination_records', function (Blueprint $table) {
            $table->foreignId('vet_id')->nullable()->constrained('vets')->nullOnDelete();
            $table->string('category')->default('Core');
        });

        Schema::create('visit_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vet_id')->constrained('vets')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('visit_date');
            $table->text('reason')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->json('prescriptions')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();

            $table->index(['vet_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_records');

        Schema::table('vaccination_records', function (Blueprint $table) {
            $table->dropForeign(['vet_id']);
            $table->dropColumn(['vet_id', 'category']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('decision_reason');
        });

        Schema::table('vets', function (Blueprint $table) {
            $table->dropColumn(['working_days', 'working_hours_start', 'working_hours_end', 'days_off']);
        });

        Schema::table('pets', function (Blueprint $table) {
            $table->dropColumn(['breed', 'weight', 'allergies']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vets', function (Blueprint $table) {
            $table->string('staff_type')->default('veterinarian');
            $table->string('specialty')->nullable();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('service_type')->default('Check-up / Consultation');
            $table->foreignId('groomer_id')->nullable()->after('vet_id')->constrained('vets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['groomer_id']);
            $table->dropColumn(['groomer_id', 'service_type']);
        });

        Schema::table('vets', function (Blueprint $table) {
            $table->dropColumn(['staff_type', 'specialty']);
        });
    }
};

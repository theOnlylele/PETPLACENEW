<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vets', function (Blueprint $table) {
            $table->string('license_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('vets')->whereNull('license_number')->update(['license_number' => '']);

        Schema::table('vets', function (Blueprint $table) {
            $table->string('license_number')->nullable(false)->change();
        });
    }
};

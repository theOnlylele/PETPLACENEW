
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE users
            MODIFY role ENUM('vet', 'owner', 'admin')
            NOT NULL DEFAULT 'owner'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE users
            MODIFY role ENUM('vet', 'owner')
            NOT NULL DEFAULT 'owner'
        ");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('email');
        });

        foreach ([
            'users' => ['name'],
            'pet_owners' => ['first_name', 'last_name'],
            'vets' => ['first_name', 'last_name'],
        ] as $table => $columns) {
            DB::table($table)->select(array_merge(['id'], $columns))
                ->orderBy('id')
                ->chunkById(100, function ($records) use ($table, $columns) {
                    foreach ($records as $record) {
                        $updates = [];

                        foreach ($columns as $column) {
                            $value = $record->{$column};

                            if ($value !== null && preg_match('/\p{L}/u', $value) === 1 && mb_strtoupper($value) === $value) {
                                $updates[$column] = Str::title(mb_strtolower(trim($value)));
                            }
                        }

                        if ($updates) {
                            DB::table($table)->where('id', $record->id)->update($updates);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
        });
    }
};

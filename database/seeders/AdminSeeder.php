<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (!$email || !$password) {
            throw new RuntimeException(
                'ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env'
            );
        }

        $user = User::firstOrNew([
            'email' => $email,
        ]);

        $user->name = env('ADMIN_NAME', 'PetPlace Administrator');
        $user->password = Hash::make($password);
        $user->role = 'admin';

        $user->save();
    }
}

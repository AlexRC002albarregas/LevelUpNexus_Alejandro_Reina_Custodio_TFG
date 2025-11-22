<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Pepe',
            'email' => 'pepe@gmail.com',
            'password' => Hash::make('1234'),
            'role' => 'player',
        ]);

        User::create([
            'name' => 'Javier',
            'email' => 'javier@gmail.com',
            'password' => Hash::make('1234'),
            'role' => 'player',
        ]);
    }
}

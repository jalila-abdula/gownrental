<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // OWNER
        User::create([
            'name' => 'Shyra Owner',
            'email' => 'owner@shyrabeautique.com',
            'password' => Hash::make('owner123'),
            'role' => 'owner',
        ]);

        // EMPLOYEE
        User::create([
            'name' => 'Shyra Employee',
            'email' => 'employee@shyrabeautique.com',
            'password' => Hash::make('employee123'),
            'role' => 'employee',
        ]);

        // CUSTOMER
        User::create([
            'name' => 'Test Customer',
            'email' => 'customer@shyrabeautique.com',
            'password' => Hash::make('customer123'),
            'role' => 'customer',
        ]);
    }
}
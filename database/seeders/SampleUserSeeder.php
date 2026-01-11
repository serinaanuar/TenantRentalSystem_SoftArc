<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SampleUserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'john.doe@example.com',
                'password' => Hash::make('password123'),
                'ic_number' => '901234567890',
                'age' => 28,
                'born_date' => Carbon::parse('1997-03-15'),
                'phone' => '012-9876543',
                'profile_picture' => null,
                'address_line_1' => '456 Elm Street',
                'address_line_2' => 'Apt 12C',
                'city' => 'Gotham',
                'postal_code' => '12345',
                'role' => 'user',
            ],
            [
                'firstname' => 'Alice',
                'lastname' => 'Smith',
                'email' => 'alice.smith@example.com',
                'password' => Hash::make('alicepass'),
                'ic_number' => '901234567891',
                'age' => 30,
                'born_date' => Carbon::parse('1993-05-20'),
                'phone' => '012-1112233',
                'profile_picture' => null,
                'address_line_1' => '789 Maple Ave',
                'address_line_2' => '',
                'city' => 'Star City',
                'postal_code' => '54321',
                'role' => 'user',
            ],
            [
                'firstname' => 'Bob',
                'lastname' => 'Johnson',
                'email' => 'bob.johnson@example.com',
                'password' => Hash::make('bobsecure'),
                'ic_number' => '901234567892',
                'age' => 25,
                'born_date' => Carbon::parse('1998-07-10'),
                'phone' => '012-2223344',
                'profile_picture' => null,
                'address_line_1' => '321 Oak Street',
                'address_line_2' => '',
                'city' => 'Central City',
                'postal_code' => '67890',
                'role' => 'user',
            ],
            [
                'firstname' => 'Clara',
                'lastname' => 'Brown',
                'email' => 'clara.brown@example.com',
                'password' => Hash::make('clarapass'),
                'ic_number' => '901234567893',
                'age' => 32,
                'born_date' => Carbon::parse('1991-02-25'),
                'phone' => '012-3334455',
                'profile_picture' => null,
                'address_line_1' => '654 Pine Lane',
                'address_line_2' => 'Unit 5A',
                'city' => 'Coast City',
                'postal_code' => '98765',
                'role' => 'user',
            ],
            [
                'firstname' => 'David',
                'lastname' => 'Lee',
                'email' => 'david.lee@example.com',
                'password' => Hash::make('davidpass'),
                'ic_number' => '901234567894',
                'age' => 29,
                'born_date' => Carbon::parse('1994-11-05'),
                'phone' => '012-4445566',
                'profile_picture' => null,
                'address_line_1' => '987 Birch Road',
                'address_line_2' => '',
                'city' => 'Blüdhaven',
                'postal_code' => '11223',
                'role' => 'user',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']], // unique email check
                array_merge($user, ['email_verified_at' => now()])
            );
        }
    }
}

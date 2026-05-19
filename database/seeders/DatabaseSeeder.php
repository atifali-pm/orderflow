<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Demo Operator',
            'email' => 'demo@orderflow.local',
        ]);

        $this->call(DemoSeeder::class);
    }
}

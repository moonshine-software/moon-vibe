<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use MoonShine\Laravel\Models\MoonshineUser;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        MoonshineUser::query()->create([
            'id' => 1,
            'name' => 'Ivan',
            'email' => 'ivan@mail.ru',
            'moonshine_user_role_id' => 1,
            'password' => Hash::make(12345),
        ]);
    }
}

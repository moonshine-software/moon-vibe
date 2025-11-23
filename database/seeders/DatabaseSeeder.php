<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use MoonShine\Laravel\Models\MoonshineUser;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        MoonshineUser::query()->create([
            'id' => 1,
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            'moonshine_user_role_id' => 1,
            'password' => Hash::make('12345'),
            'settings' => '{"build": {"repository": "https://github.com/dev-lnk/moonshine-blank-v4.git"}, "generation": {"attempts": 5}}'
        ]);
    }
}

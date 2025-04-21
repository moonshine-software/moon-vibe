<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use MoonShine\Laravel\Models\MoonshineUserRole;

return new class extends Migration
{
    public function up(): void
    {
        MoonshineUserRole::query()->forceCreate([
            'id' => Role::USER->value,
            'name' => 'User'
        ]);
    }

    public function down(): void
    {
        MoonshineUserRole::query()->where('id', Role::USER->value)->delete();
    }
};

<?php

use App\Enums\SubscriptionPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('subscription_plans')->insert([
            'name' => 'Monthly',
            'generations_limit' => 10,
            'period' => SubscriptionPeriod::MONTHLY->value,
        ]);
    }

    public function down(): void
    {
        DB::table('subscription_plans')->where('name', 'Monthly')->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moonshine_users', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')
                ->after('lang')
                ->nullable()
                ->constrained('subscription_plans')
                ->nullOnDelete();
                  
            $table->unsignedInteger('generations_used')->default(0)->after('subscription_plan_id');
            $table->date('subscription_end_date')->nullable()->after('generations_used');
        });
    }

    public function down(): void
    {
        Schema::table('moonshine_users', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn('subscription_plan_id');
            $table->dropColumn('generations_used');
            $table->dropColumn('subscription_end_date');
        });
    }
};

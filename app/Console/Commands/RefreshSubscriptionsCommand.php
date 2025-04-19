<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MoonShineUser;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RefreshSubscriptionsCommand extends Command
{
    protected $signature = 'app:refresh-subscriptions';

    public function handle(): int
    {
        /** @var Collection<int, MoonShineUser> $users */
        $users = MoonShineUser::query()
            ->whereNotNull('subscription_plan_id')
            ->get();

        if ($users->count() === 0) {
            return self::SUCCESS;
        }

        foreach ($users as $user) {
            if($user->subscription_end_date > now()) {
                continue;
            }
            
            $newSubscriptionEndDate = now()->add("+ {$user->subscriptionPlan->period->getPeriod()}");

            $user->subscription_end_date = $newSubscriptionEndDate;
            $user->generations_used = 0;
            $user->save();
        }

        return self::SUCCESS;
    }
} 
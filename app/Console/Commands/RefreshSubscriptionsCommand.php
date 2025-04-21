<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MoonShineUser;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Services\Subscription\SubscriptionService;

class RefreshSubscriptionsCommand extends Command
{
    protected $signature = 'app:refresh-subscriptions';

    public function __construct(
        private SubscriptionService $subscriptionService,
    ) {
        parent::__construct();
    }   

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
            $this->subscriptionService->refresh($user);
        }

        return self::SUCCESS;
    }
} 
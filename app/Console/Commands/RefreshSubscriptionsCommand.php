<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\MoonShineUser;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class RefreshSubscriptionsCommand extends Command
{
    protected $signature = 'app:refresh-subscriptions';

    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        /** @var Collection<array-key, MoonShineUser> $users */
        $users = MoonShineUser::query()
            ->whereNotNull('subscription_plan_id')
            ->where('role', Role::USER->value)
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

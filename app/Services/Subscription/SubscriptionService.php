<?php

namespace App\Services\Subscription;

use App\Models\MoonShineUser;
use App\Exceptions\UserPlanException;

class SubscriptionService
{
    /**
     * @throws UserPlanException
     */
    public function validate(MoonShineUser $user): void
    {   
        $subscriptionPlan = $user->subscriptionPlan;
        if($user->subscription_end_date === null) {
            throw new UserPlanException(__('app.subscription_plan_not_found'));
        } 
        
        if($user->subscription_end_date <= now()) {
            throw new UserPlanException(__('app.subscription_expired'));
        }

        if($user->generations_used >= $subscriptionPlan->generations_limit) {
            throw new UserPlanException(__('app.generations_limit_exceeded'));
        }
    }

    public function increaseGenerationsUsed(MoonShineUser $user): void
    {
        $user->generations_used++;
        $user->save();
        $user->refresh();
    }

    public function refresh(MoonShineUser $user): void
    {
        if(
            $user->subscription_plan_id === null ||
            $user->subscription_end_date > now()
        ) {
            return;
        }

        // TODO Проверка, что можем продлить подписку

        $user->subscription_end_date = now()->add("+ {$user->subscriptionPlan->period->getPeriod()}");
        $user->generations_used = 0;
        $user->save();
    }   
}
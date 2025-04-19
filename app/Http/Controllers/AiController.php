<?php

namespace App\Http\Controllers;

use Throwable;
use App\Actions\CorrectFromAI;
use App\Actions\GenerateFromAI;
use App\Exceptions\UserPlanException;
use Illuminate\Http\RedirectResponse;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Laravel\Pages\Crud\FormPage;
use App\MoonShine\Resources\ProjectResource;
use MoonShine\Laravel\Http\Controllers\MoonShineController;

class AiController extends MoonShineController
{
    public function index(GenerateFromAI $action): RedirectResponse
    {
        $data = request()->validate([
            'prompt' => ['string', 'required', 'min:10'],
            'project_name' => ['string', 'required'],
        ]);

        try {
            $this->updateUserGenerationsUsed();
        } catch (UserPlanException $e) {
            $this->toast($e->getMessage(), ToastType::ERROR);
            return back();
        }

        $projectId = $action->handle(
            $data['project_name'],
            $data['prompt'],
            auth('moonshine')->user(),
            app()->getLocale()
        );
        
        return toPage(
            FormPage::class,
            ProjectResource::class,
            params: ['resourceItem' => $projectId],
            redirect: true
        );
    }

    public function correct(int $schemaId, CorrectFromAI $action)
    {
        $data = request()->validate([
            'prompt' => ['string', 'required', 'min:5']
        ]);

        try {
            $this->updateUserGenerationsUsed();
        } catch (UserPlanException $e) {
            $this->toast($e->getMessage(), ToastType::ERROR);
            return back();
        }

        $action->handle($schemaId, $data['prompt'], auth('moonshine')->user(), app()->getLocale());

        return back();
    }

    /**
     * @throws UserPlanException
     */
    private function updateUserGenerationsUsed(): void
    {
        $user = auth('moonshine')->user();

        $subscriptionPlan = $user->subscriptionPlan;
        if($subscriptionPlan === null) {
            throw new UserPlanException(__('app.subscription_plan_not_found'));
        } 
        
        if($user->subscription_end_date <= now()) {
            throw new UserPlanException(__('app.subscription_expired'));
        }

        if($user->generations_used >= $subscriptionPlan->generations_limit) {
            throw new UserPlanException(__('app.generations_limit_exceeded'));
        }

        $user->generations_used++;
        $user->save();
    }
}

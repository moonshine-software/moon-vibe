<?php

namespace App\Http\Controllers;


use App\Actions\CorrectFromAI;
use App\Actions\GenerateFromAI;
use App\Actions\ValidatePendingSchemas;
use App\Exceptions\GenerateException;
use App\Exceptions\UserPlanException;
use Illuminate\Http\RedirectResponse;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Laravel\Pages\Crud\FormPage;
use App\MoonShine\Resources\ProjectResource;
use App\Services\Subscription\SubscriptionService;
use MoonShine\Laravel\Http\Controllers\MoonShineController;

class AiController extends MoonShineController
{
    public function index(
        GenerateFromAI $generateAction,
        SubscriptionService $subscriptionService,
        ValidatePendingSchemas $validatePendingSchemas
    ): RedirectResponse {
        $data = request()->validate([
            'prompt' => ['string', 'required', 'min:10'],
            'project_name' => ['string', 'required'],
        ]);

        try {
            $subscriptionService->validate(auth('moonshine')->user());
        } catch (UserPlanException $e) {
            $this->toast($e->getMessage(), ToastType::ERROR);
            return back();
        }

        $subscriptionService->increaseGenerationsUsed(auth('moonshine')->user());

        try {
            $validatePendingSchemas->handle(auth('moonshine')->user()->id);
        } catch (GenerateException $e) {
            $this->toast($e->getMessage(), ToastType::ERROR);
            return back();
        }

        $projectId = $generateAction->handle(
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

    public function correct(
        int $schemaId,
        CorrectFromAI $correctAction,
        SubscriptionService $subscriptionService,
        ValidatePendingSchemas $validatePendingSchemas
    ): RedirectResponse {
        $data = request()->validate([
            'prompt' => ['string', 'required', 'min:5']
        ]);

        try {
            $subscriptionService->validate(auth('moonshine')->user());
        } catch (UserPlanException $e) {
            $this->toast($e->getMessage(), ToastType::ERROR);
            return back();
        }

        try {
            $validatePendingSchemas->handle(auth('moonshine')->user()->id);
        } catch (GenerateException $e) {
            $this->toast($e->getMessage(), ToastType::ERROR);
            return back();
        }

        $subscriptionService->increaseGenerationsUsed(auth('moonshine')->user());

        $correctAction->handle($schemaId, $data['prompt'], auth('moonshine')->user(), app()->getLocale());

        return back();
    }
}

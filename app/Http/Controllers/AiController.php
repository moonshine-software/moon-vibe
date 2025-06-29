<?php

namespace App\Http\Controllers;

use App\Actions\CorrectFromAI;
use App\Actions\GenerateFromAI;
use App\Exceptions\GenerateException;
use App\Exceptions\UserPlanException;
use App\Models\MoonShineUser;
use App\MoonShine\Resources\ProjectResource;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\Enums\ToastType;

class AiController extends MoonShineController
{
    public function index(
        GenerateFromAI $generateAction,
        SubscriptionService $subscriptionService
    ): RedirectResponse {
        $data = request()->validate([
            'prompt' => ['string', 'required', 'min:10'],
            'llm_id' => ['int', 'required'],
            'project_name' => ['string', 'required'],
        ]);

        /** @var MoonShineUser $user */
        $user = auth('moonshine')->user();

        try {
            $subscriptionService->validate($user);
        } catch (UserPlanException $e) {
            $this->toast($e->getMessage(), ToastType::ERROR);

            return back();
        }

        try {
            $projectId = $generateAction->handle(
                $data['project_name'],
                (int) $data['llm_id'],
                $data['prompt'],
                $user,
                app()->getLocale()
            );
        } catch (GenerateException $e) {
            $this->toast($e->getMessage(), ToastType::ERROR);

            return back()->withInput();
        }

        $subscriptionService->increaseGenerationsUsed($user);

        /** @var RedirectResponse $toPage */
        $toPage = toPage(
            FormPage::class,
            ProjectResource::class,
            params: ['resourceItem' => $projectId],
            redirect: true
        );

        return $toPage;
    }

    public function correct(
        int $schemaId,
        CorrectFromAI $correctAction,
        SubscriptionService $subscriptionService,
    ): RedirectResponse {
        $data = request()->validate([
            'prompt' => ['string', 'required', 'min:5'],
        ]);

        /** @var MoonShineUser $user */
        $user = auth('moonshine')->user();

        try {
            $subscriptionService->validate($user);
        } catch (UserPlanException $e) {
            $this->toast($e->getMessage(), ToastType::ERROR);

            return back();
        }

        try {
            $correctAction->handle($schemaId, $data['prompt'], $user, app()->getLocale());
        } catch (GenerateException $e) {
            $this->toast($e->getMessage(), ToastType::ERROR);

            return back();
        }

        $subscriptionService->increaseGenerationsUsed($user);

        return back();
    }
}

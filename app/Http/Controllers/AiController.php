<?php

namespace App\Http\Controllers;

use App\Actions\CorrectFromAI;
use App\Actions\GenerateFromAI;
use App\MoonShine\Resources\ProjectResource;
use Illuminate\Http\RedirectResponse;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use MoonShine\Laravel\Pages\Crud\FormPage;
use Throwable;

class AiController extends MoonShineController
{
    /**
     * @throws Throwable
     */
    public function index(GenerateFromAI $action): RedirectResponse
    {
        $data = request()->validate([
            'prompt' => ['string', 'required'],
            'project_name' => ['string', 'required'],
        ]);
        
        $projectId = $action->handle(
            $data['project_name'],
            $data['prompt'],
            (int) auth('moonshine')->user()->id,
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
            'prompt' => ['string', 'required']
        ]);

        $action->handle($schemaId, $data['prompt'], app()->getLocale());

        return back();
    }
}

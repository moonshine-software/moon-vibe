<?php

namespace App\Http\Controllers;

use App\Actions\GenerateFromAI;
use App\Models\Project;
use App\MoonShine\Resources\ProjectResource;
use App\Services\RequestAdminAi;
use App\Support\SchemaValidator;
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
            'promt' => ['string', 'required'],
            'project_name' => ['string', 'required'],
        ]);

        $projectId = $action->handle(
            $data['project_name'],
            $data['promt'],
            (int) auth('moonshine')->user()->id
        );

        return toPage(
            FormPage::class,
            ProjectResource::class,
            params: ['resourceItem' => $projectId],
            redirect: true
        );
    }
}

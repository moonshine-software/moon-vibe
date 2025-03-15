<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\MoonShine\Resources\ProjectResource;
use App\Services\RequestAdminAi;
use App\Support\SchemaValidator;
use Illuminate\Http\RedirectResponse;
use JsonException;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use MoonShine\Laravel\Pages\Crud\FormPage;
use Throwable;

class AiController extends MoonShineController
{
    /**
     * @throws Throwable
     */
    public function index(RequestAdminAi $requestAdminAi): RedirectResponse
    {
        $data = request()->validate([
            'promt' => ['string', 'required'],
            'project_name' => ['string', 'required'],
        ]);

        $project = Project::query()->create([
            'name' => $data['project_name'],
            'moonshine_user_id' => auth('moonshine')->user()->id
        ]);

        $schema = $requestAdminAi->send($data['promt']);

        $error = '';
        try {
            (new SchemaValidator($schema))
                ->validate();
        }catch (Throwable $e) {
            $error = $e->getMessage();
        }

        $project->schemas()->create([
            'error' => $error,
            'schema' => $schema
        ]);

        return toPage(
            FormPage::class,
            ProjectResource::class,
            params: ['resourceItem' => $project->getKey()],
            redirect: true
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Jobs\ProcessBuildJob;
use App\Models\ProjectSchema;
use App\MoonShine\Components\ProjectBuildComponent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use MoonShine\Support\Enums\ToastType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use App\Enums\BuildStatus;

class BuildController extends MoonShineController
{
    public function index(int $schemaId): JsonResponse
    {
        $projectSchema = ProjectSchema::query()->where('id', $schemaId)->first();

        if (! $projectSchema) {
            return $this->json('Схема проекта не найдена', messageType: ToastType::ERROR);
        }

        Build::query()->where('moonshine_user_id', $this->auth()->id())->delete();

        $build = Build::create([
            'project_schema_id' => $projectSchema->id,
            'moonshine_user_id' => $this->auth()->id(),
            'status_id' => BuildStatus::IN_PROGRESS,
        ]);

        dispatch(new ProcessBuildJob($build, app()->getLocale()));

        return $this->json()
            ->htmlData(
                (string) ProjectBuildComponent::fromBuild($build),
                '#build-component-' . $projectSchema->project->id
            )
        ;
    }
    
    public function download(int $buildId): BinaryFileResponse|RedirectResponse
    {
        $build = Build::query()->where('id', $buildId)->first();
        
        if ($build === null) {
            return back()->with('error', 'Сборка не найдена');
        }
        
        if ($build->status_id !== BuildStatus::COMPLETED) {
            return back()->with('error', 'Сборка еще не готова или завершилась с ошибкой');
        }
        
        if (! file_exists($build->file_path)) {
            return back()->with('error', 'Файл сборки не найден');
        }
        
        return response()->download($build->file_path);
    }
}

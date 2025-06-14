<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Jobs\ProcessBuildJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use App\Enums\BuildStatus;

class BuildController extends MoonShineController
{
    public function index(int $schemaId): Response
    {
        dispatch(new ProcessBuildJob(
            $schemaId,
            (int) $this->auth()->user()?->id,
            app()->getLocale())
        );

        return response()->noContent(200);
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

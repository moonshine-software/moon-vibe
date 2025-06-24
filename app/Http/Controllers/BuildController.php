<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTestBuildJob;
use App\Models\Build;
use App\Jobs\ProcessDownloadBuildJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use App\Enums\BuildStatus;

class BuildController extends MoonShineController
{
    public function forDownload(int $schemaId): Response
    {
        dispatch(new ProcessDownloadBuildJob(
            $schemaId,
            (int) $this->auth()->user()?->id,
            app()->getLocale())
        );

        return response()->noContent(200);
    }

    public function forTest(int $schemaId): Response
    {
        dispatch(new ProcessTestBuildJob(
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
        
        if ($build->status_id !== BuildStatus::FOR_DOWNLOAD) {
            return back()->with('error', 'Сборка еще не готова или завершилась с ошибкой');
        }
        
        if (! file_exists($build->file_path)) {
            return back()->with('error', 'Файл сборки не найден');
        }
        
        return response()->download($build->file_path);
    }
}

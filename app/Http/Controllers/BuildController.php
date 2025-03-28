<?php

namespace App\Http\Controllers;

use App\Models\ProjectSchema;
use App\Services\MakeAdmin\MakeAdmin;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BuildController extends MoonShineController
{
    public function index(int $schemaId): BinaryFileResponse
    {
        $projectSchema = ProjectSchema::query()->where('id', $schemaId)->first();

        $makeAdmin = new MakeAdmin(
            $projectSchema->schema,
            Storage::disk('local')->path('builds/' . $this->auth()->id())
        );

        $path = $makeAdmin->handle();

        return response()->download($path);
    }
}

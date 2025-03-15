<?php

namespace App\Http\Controllers;

use App\Models\ProjectSchema;
use App\Services\MakeAdmin;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BuildController extends MoonShineController
{
    public function index(int $schemaId): BinaryFileResponse
    {
        $projectSchema = ProjectSchema::query()->where('id', $schemaId)->first();

        $filePath = base_path('/results/item_' . time() . '.json');

        file_put_contents($filePath, $projectSchema->schema);

        $makeAdmin = new MakeAdmin($filePath);
        $path = $makeAdmin->handle();

        return response()->download($path);
    }
}

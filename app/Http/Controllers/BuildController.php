<?php

namespace App\Http\Controllers;

use App\Models\ProjectSchema;
use App\Services\MakeAdmin\MakeAdmin;
use App\Support\SchemaValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use MoonShine\Support\Enums\ToastType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BuildController extends MoonShineController
{
    public function index(int $schemaId): BinaryFileResponse|RedirectResponse
    {
        $projectSchema = ProjectSchema::query()->where('id', $schemaId)->first();

        $errors = (new SchemaValidator($projectSchema->schema))->validate();
        if($errors !== '') {
            return back();
        }

        $makeAdmin = new MakeAdmin(
            $projectSchema->schema,
            Storage::disk('local')->path('builds/' . $this->auth()->id())
        );

        $path = $makeAdmin->handle();

        return response()->download($path);
    }
}

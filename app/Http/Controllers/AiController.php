<?php

namespace App\Http\Controllers;

use App\Services\MakeAdmin;
use App\Services\RequestAdminAi;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AiController extends MoonShineController
{
    public function index(RequestAdminAi $requestAdminAi): BinaryFileResponse
    {
        $schema = $requestAdminAi->send(request()->input(['promt']));

        $filePath = base_path('/results/item_' . time() . '.json');

        file_put_contents($filePath, $schema);

        //$filePath = base_path('results/item_1740752589.json');

        $makeAdmin = new MakeAdmin($filePath);
        $path = $makeAdmin->handle();

        return response()->download($path);
    }
}

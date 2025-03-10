<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionsHandler
{
    public function __invoke(Exceptions $exceptions): Exceptions
    {
        $exceptions->renderable(
            function (NotFoundHttpException $e, ?Request $request = null) {
                if ($request?->is('api/*')) {
                    return $this->jsonResponse($e->getStatusCode());
                }

                return response()->view('errors.404', status: $e->getStatusCode());
            }
        );

        return $exceptions;
    }

    private function jsonResponse(int $code): JsonResponse
    {
        return response()->json([
            'error' => "HTTP error: $code",
        ])->setStatusCode($code);
    }
}

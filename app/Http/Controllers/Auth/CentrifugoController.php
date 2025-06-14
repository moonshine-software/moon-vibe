<?php

namespace App\Http\Controllers\Auth;

use App\Models\MoonShineUser;
use Illuminate\Http\JsonResponse;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use phpcent\Client;
use Illuminate\Support\Facades\Cache;

class CentrifugoController extends MoonShineController
{
    // 1 day
    private const TOKEN_EXPIRATION = 24 * 60 * 60;
    
    public function index(): JsonResponse
    {
        /** @var MoonShineUser $user */
        $user = $this->auth()->user();
        $userId = $user->id;
        
        $cacheKey = "centrifugo_token_{$userId}";
        $cachedToken = Cache::get($cacheKey);
        
        if (! empty($cachedToken)) {
            return response()->json([
                'token' => $cachedToken
            ]);
        }
        
        $client = new Client(
            config('app.centrifugo.host'). '/api',
            config('app.centrifugo.api-key'),
            config('app.centrifugo.secret')
        );
        
        $expiresAt = time() + self::TOKEN_EXPIRATION;
        $token = $client->generateConnectionToken((string) $userId, $expiresAt);
        
        Cache::put($cacheKey, $token, self::TOKEN_EXPIRATION);
        
        return response()->json([
            'token' => $token
        ]);
    }
}

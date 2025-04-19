<?php

namespace App\Http\Controllers\Auth;

use MoonShine\Laravel\Http\Controllers\MoonShineController;
use phpcent\Client;
use Illuminate\Support\Facades\Cache;

class CentrifugoController extends MoonShineController
{
    // 1 day
    private const TOKEN_EXPIRATION = 24 * 60 * 60;
    
    public function index()
    {
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
        $token = $client->generateConnectionToken($userId, $expiresAt);
        
        Cache::put($cacheKey, $token, self::TOKEN_EXPIRATION);
        
        return response()->json([
            'token' => $token
        ]);
    }
}

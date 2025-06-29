<?php

/**
 * @see https://github.com/centrifugal/phpcent
 */

declare(strict_types=1);

namespace App\Services;

use Throwable;
use phpcent\Client;
use MoonShine\Twirl\DTO\TwirlData;
use MoonShine\Twirl\Contracts\TwirlBroadcastContract;

final class Centrifugo implements TwirlBroadcastContract
{
    public function send(string $channel, TwirlData $twirlData): void
    {
        try {
            $client = new Client(config('app.centrifugo.host'). '/api', config('app.centrifugo.api-key'));
            $client->publish($channel, $twirlData->toArray());
        } catch (Throwable $e) {
            report($e);
        }
    }
}
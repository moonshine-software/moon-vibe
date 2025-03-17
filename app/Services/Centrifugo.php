<?php

/**
 * @see https://github.com/centrifugal/phpcent
 */

declare(strict_types=1);

namespace App\Services;

use MoonShine\Rush\Contracts\RushBroadcastContract;
use MoonShine\Rush\DTO\RushData;
use phpcent\Client;
use Throwable;

final class Centrifugo implements RushBroadcastContract
{
    public function send(string $channel, RushData $rushData): void
    {
        try {
            $client = new Client(config('app.centrifugo.host'). '/api', config('app.centrifugo.api-key'));
            $client->publish($channel, $rushData->toArray());
        } catch (Throwable $e) {
            report($e);
        }
    }
}
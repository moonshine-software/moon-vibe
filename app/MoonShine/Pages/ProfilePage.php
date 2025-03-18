<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Components\Layout\Profile;
use MoonShine\Laravel\Pages\Page;

class ProfilePage extends Page
{

    protected function components(): iterable
    {
        return [
            Profile::make(withBorder: true),
        ];
    }
}
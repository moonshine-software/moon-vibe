<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\RegisterFormRequest;
use App\Models\MoonShineUser;
use App\MoonShine\Pages\Auth\RegisterPage;
use Illuminate\Http\RedirectResponse;
use MoonShine\Laravel\Http\Controllers\MoonShineController;

class RegisterController extends MoonShineController
{
    public function form(RegisterPage $page): RegisterPage
    {
        return $page;
    }

    public function store(RegisterFormRequest $request): RedirectResponse
    {
        $user = MoonShineUser::query()->create(
            $request->validated()
        );

        $this->auth()->login($user);

        return redirect()->intended();
    }
}

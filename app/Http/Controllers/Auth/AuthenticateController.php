<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\AuthenticateFormRequest;
use App\MoonShine\Pages\Auth\LoginPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use MoonShine\Laravel\Http\Controllers\MoonShineController;

class AuthenticateController extends MoonShineController
{
    public function form(LoginPage $page): LoginPage
    {
        return $page;
    }

    public function authenticate(AuthenticateFormRequest $request): RedirectResponse
    {
        if(! $this->auth()->attempt($request->validated())) {
            return back()->withErrors([
                'email' => __('moonshine::auth.failed')
            ])->withInput();
        }

        return redirect()->intended();
    }

    public function logout(
        Request $request
    ): RedirectResponse {
        $this->auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->intended(
            url()->previous() ? url()->previous() : route('/')
        );
    }
}

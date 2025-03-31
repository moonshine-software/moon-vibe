<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordFormRequest;
use App\MoonShine\Pages\Auth\ForgotPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use MoonShine\Laravel\MoonShineUI;

class ForgotController extends Controller
{
    public function form(ForgotPage $page): ForgotPage
    {
        return $page;
    }

    public function reset(ForgotPasswordFormRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            MoonShineUI::toast(__('If the account exists, then the instructions are sent to your email'));
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['alert' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }
}
<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Auth;

use App\MoonShine\Layouts\FormLayout;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\PasswordRepeat;
use MoonShine\UI\Fields\Text;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template-extends Page<null>
 */
class ResetPasswordPage extends Page
{
    protected ?string $layout = FormLayout::class;

    protected function booted(): void
    {
        throw new NotFoundHttpException();
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Reset password';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            FormBuilder::make()
                ->class('authentication-form')
                ->action(route('password.update'))
                ->fields([
                    Hidden::make('token')->setValue(request()->route('token')),

                    Text::make('E-mail', 'email')
                        ->setValue(request()->input('email'))
                        ->required()
                        ->readonly(),

                    Password::make(__('app.auth.password'), 'password')
                        ->required(),

                    PasswordRepeat::make(__('app.auth.repeat_password'), 'password_confirmation')
                        ->required(),
                ])->submit(__('app.auth.reset_password'), [
                    'class' => 'btn-primary btn-lg w-full',
                ]),
        ];
    }
}

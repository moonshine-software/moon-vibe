<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Auth;

use App\MoonShine\Layouts\FormLayout;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\PasswordRepeat;
use MoonShine\UI\Fields\Text;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template-extends Page<null>
 */
class RegisterPage extends Page
{
    protected ?string $layout = FormLayout::class;

    protected function booted(): void
    {
        throw new NotFoundHttpException();
    }

    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Register';
    }


    protected function components(): iterable
    {
        return [
            FormBuilder::make()
                ->class('authentication-form')
                ->action(route('register.store'))
                ->fields([
                    Text::make(__('app.auth.name'), 'name')->required(),
                    Text::make('E-mail', 'email')
                        ->required()
                        ->customAttributes([
                            'autofocus' => true,
                            'autocomplete' => 'off',
                        ]),

                    Password::make(__('app.auth.password'), 'password')
                        ->required(),

                    PasswordRepeat::make(__('app.auth.repeat_password'), 'password_confirmation')
                        ->required(),
                ])->submit(__('app.auth.create_account'), [
                    'class' => 'btn-primary btn-lg w-full',
                ])->buttons([
                    ActionButton::make(__('app.auth.log_in'), route('login')),
                ]),
        ];
    }
}

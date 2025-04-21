<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Auth;

use App\MoonShine\Layouts\FormLayout;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Fields\Text;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template-extends Page<null>
 */
class ForgotPage extends Page
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
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Forgot password';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            FormBuilder::make()
                ->class('authentication-form')
                ->action(route('forgot'))
                ->fields([
                    Text::make('E-mail', 'email')
                        ->required()
                        ->customAttributes([
                            'autofocus' => true,
                            'autocomplete' => 'off',
                        ]),
                ])->submit(__('app.auth.reset_password'), [
                    'class' => 'btn-primary btn-lg w-full',
                ]),

            Divider::make(),

            Flex::make([
                ActionButton::make(__('app.auth.log_in'), route('login'))->primary(),
            ])->justifyAlign('start')
        ];
    }
}

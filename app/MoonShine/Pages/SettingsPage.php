<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use LogicException;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Fields\Password;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\PasswordRepeat;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\TypeCasts\ModelCaster;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Traits\WithComponentsPusher;
use MoonShine\Laravel\Http\Controllers\ProfileController;

#[SkipMenu]
/**
 * @extends Page<null>
 */
class SettingsPage extends Page
{
    use WithComponentsPusher;

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
        return __('moonshine::ui.profile');
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        $userFields = array_filter([
            ID::make()->sortable(),

            moonshineConfig()->getUserField('name')
                ? Text::make(__('moonshine::ui.resource.name'), moonshineConfig()->getUserField('name'))
                ->required()
                : null,

            moonshineConfig()->getUserField('username')
                ? Text::make(__('moonshine::ui.login.username'), moonshineConfig()->getUserField('username'))
                ->required()
                : null,

            moonshineConfig()->getUserField('avatar')
                ? Image::make(__('moonshine::ui.resource.avatar'), moonshineConfig()->getUserField('avatar'))
                ->disk(moonshineConfig()->getDisk())
                ->options(moonshineConfig()->getDiskOptions())
                ->dir('moonshine_users')
                ->removable()
                ->allowedExtensions(['jpg', 'png', 'jpeg', 'gif'])
                : null,

            Select::make('Language', 'lang')->options([
                'en' => 'English',
                'ru' => 'Russian',
            ])->default('en'),
        ]);

        $userPasswordsFields = moonshineConfig()->getUserField('password') ? [
            Heading::make(__('moonshine::ui.resource.change_password')),

            Password::make(__('moonshine::ui.resource.password'), moonshineConfig()->getUserField('password'))
                ->customAttributes(['autocomplete' => 'new-password'])
                ->eye(),

            PasswordRepeat::make(__('moonshine::ui.resource.repeat_password'), 'password_repeat')
                ->customAttributes(['autocomplete' => 'confirm-password'])
                ->eye(),
        ] : [];

        $userFields = array_merge($userFields, $userPasswordsFields);

        $user = MoonShineAuth::getGuard()->user() ?? MoonShineAuth::getModel();

        $generateFields = [
            Number::make(__('app.settings.max_attempts'), 'attempts')
                ->default($user->settings['generation']['attempts'] ?? 5),
        ];

        return [
            Box::make([
                Tabs::make([
                    Tab::make(__('app.settings.profile'), $userFields),
                    Tab::make(__('app.settings.generation'), $generateFields),
//                    Tab::make(__('app.settings.deployment'), [
//                        Text::make(__('app.settings.repository'), 'repository')
//                            ->default(
//                                $user->settings['build']['repository'] ?? 'https://github.com/dev-lnk/moonshine-blank.git'
//                            ),
//                    ]),
                ]),
            ]),
        ];
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            $this->getForm(),
            ...$this->getPushedComponents(),
        ];
    }

    public function getForm(): FormBuilderContract
    {
        $user = MoonShineAuth::getGuard()->user() ?? MoonShineAuth::getModel();

        if (\is_null($user)) {
            throw new LogicException('Model is required');
        }

        return FormBuilder::make(action([ProfileController::class, 'store']))
            ->async()
            ->fields($this->fields())
            ->fillCast($user, new ModelCaster($user::class))
            ->submit(__('moonshine::ui.save'), [
                'class' => 'btn-lg btn-primary',
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\UI\Components\{Components,
    Layout\Body,
    Layout\Div,
    Layout\Flash,
    Layout\Html,
    Layout\Layout};

final class FormLayout extends MoonShineLayout
{
    protected function getHomeUrl(): string
    {
        return route('/');
    }

    public function build(): Layout
    {
        return Layout::make([
            Html::make([
                $this->getHeadComponent(),
                Body::make([
                    Div::make([
                        Div::make([
                            Flash::make(),
                            Components::make($this->getPage()->getComponents()),
                        ])->class('authentication-content'),
                    ])
                        ->class('authentication')
                        ->customAttributes([
                            'style' => 'padding: inherit; background-image: url(/images/back.png); background-size: cover; background-position: center;'
                        ])
                    ,
                ]),
            ])
                ->customAttributes([
                    'lang' => $this->getHeadLang(),
                ])
                ->withAlpineJs()
                ->withThemes(),
        ]);
    }
}

<?php

namespace App\MoonShine\Pages\Project;

use App\MoonShine\Components\ProjectBuildComponent;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Div;

class ProjectFormPage extends FormPage
{
    public function components(): iterable
    {
        if ($this->getResource()?->getItemID() === null) {
            return parent::components();
        }

        $buildComponent = ProjectBuildComponent::fromData(
            (int) auth('moonshine')->user()?->id,
            (int) $this->getResource()->getItemID()
        );

        $buildComponents = $buildComponent === '' ? [] : [$buildComponent];

        return [
            Div::make($buildComponents)->customAttributes([
                'id' => 'build-component-' . (int) $this->getResource()->getItemID(),
            ]),
            ...parent::components(),
        ];
    }
}

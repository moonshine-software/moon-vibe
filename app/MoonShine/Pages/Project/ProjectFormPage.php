<?php

namespace App\MoonShine\Pages\Project;

use App\MoonShine\Components\ProjectBuildComponent;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Div;

class ProjectFormPage extends FormPage
{
    public function components(): iterable
    {
        if($this->getResource()->getItemID() === null) {
            return parent::components();
        }

        $buildComponent = ProjectBuildComponent::fromData(
            auth('moonshine')->user()->id,
            $this->getResource()->getItemID()
        );
    
        return [
            Div::make([
                $buildComponent
            ])->customAttributes([
                'id' => 'build-component-' . $this->getResource()->getItemID()
            ]),
            ...parent::components(),
        ];
    }
}

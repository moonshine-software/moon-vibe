<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Schema;

use App\Services\SimpleSchema;
use DevLnk\MoonShineBuilder\Services\CodeStructure\Factories\StructureFromArray;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Preview;

class SchemaDetailPage extends DetailPage
{
    public function components(): iterable
    {
        $schema = $this->getResource()->getItem();

        if($schema === null) {
            return [];
        }

        $simpleSchema = new SimpleSchema((new StructureFromArray(json_decode($schema->schema, true)))->makeStructures());

        return [
            Preview::make('Схема')->setValue($simpleSchema->generate())
        ];
    }
}
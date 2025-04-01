<?php

declare(strict_types=1);

namespace App\Services;

use DevLnk\MoonShineBuilder\Services\CodeStructure\CodeStructureList;
use DevLnk\MoonShineBuilder\Support\TypeMap;

readonly class SimpleSchema
{
    public function __construct(
        private CodeStructureList $codeStructureList
    ) {

    }

    public function generate(bool $withColumns = true): string
    {
        $typeMap = new TypeMap();

        $result = '<ul>';

        foreach ($this->codeStructureList->codeStructures() as $codeStructure) {
            $result .= str('<li>')
                ->when($withColumns, fn($str) => $str->append('<b>'))
                ->append("{$codeStructure->entity()->ucFirstSingular()}")
                ->when($withColumns, fn($str) => $str->append('</b>'))
                ->append(' - ')
                ->append("menu: <b>{$codeStructure->menuName()}</b>, ")
                ->append("column: <b>{$codeStructure->getColumnName()}</b>, ")
                ->append("table: <b>{$codeStructure->table()}</b>, ")
            ;
            if($withColumns) {
                $result .= '<ul style="margin-left: 2rem">';
                foreach ($codeStructure->columns() as $column) {

                    $fieldClass = $column->getFieldClass()
                        ? $typeMap->fieldClassFromAlias($column->getFieldClass())
                        : $typeMap->getMoonShineFieldFromSqlType($column->type())
                    ;
                    $fieldClassNamespace = explode("\\", $fieldClass);
                    $field = array_pop($fieldClassNamespace);

                    $result .= str('<li>')
                        ->append("\t")
                        ->append("<b>{$column->name()}</b>")
                        ->append(' - ')
                        ->append("column: <b>{$column->column()}</b>, ")
                        ->append("type: <b>{$column->type()->value}</b>, ")
                        ->append("field: <b>$field</b>")
                        ->append("</li>");
                }
                $result .= '</ul>';
            }
            $result .= '</li>';
        }
        $result .= '</ul>';

        return $result;
    }
}
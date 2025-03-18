<?php

declare(strict_types=1);

namespace App\Services;

use DevLnk\MoonShineBuilder\Services\CodeStructure\CodeStructureList;

readonly class SimpleSchema
{
    public function __construct(
        private CodeStructureList $codeStructureList
    ) {

    }

    public function generate(bool $withColumns = true): string
    {
        $result = '<ul>';

        foreach ($this->codeStructureList->codeStructures() as $codeStructure) {
            $result .= str('<li>')
                ->when($withColumns, fn($str) => $str->append('<b>'))
                ->append($codeStructure->menuName())
                ->when($withColumns, fn($str) => $str->append('</b>'))
            ;
            if($withColumns) {
                $result .= '<ul style="margin-left: 2rem">';
                foreach ($codeStructure->columns() as $column) {
                    $result .= str('<li>')
                        ->append("\t")
                        ->append("{$column->name()}")
                        ->append(' (')
                        ->append($column->column())
                        ->append(':')
                        ->append($column->type()->value)
                        ->append(')')
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
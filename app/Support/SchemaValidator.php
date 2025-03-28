<?php

declare(strict_types=1);

namespace App\Support;

use DevLnk\MoonShineBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\MoonShineBuilder\Support\TypeMap;
use Throwable;
use ReflectionClass;
use DevLnk\MoonShineBuilder\Enums\SqlTypeMap;
use DevLnk\MoonShineBuilder\Services\CodeStructure\ColumnStructure;
use DevLnk\MoonShineBuilder\Services\CodeStructure\Factories\StructureFromArray;

readonly class SchemaValidator
{
    public function __construct(
        private string $schema
    ) {
    }

    public function validate(): string
    {
        $errors = [];

        try {
            if (! json_validate($this->schema)) {
                return 'Некорректная JSON схема';
            }

            $data = json_decode($this->schema, true);

            $factory = new StructureFromArray($data);

            $codeStructures = $factory->makeStructures();

            if($codeStructures->codeStructures() === []) {
                return 'Не удалось получить ни одного ресурса';
            }

            foreach ($codeStructures->codeStructures() as $index => $codeStructure) {
                if($codeStructure->columns() === []) {
                    $errors[] = "В ресурсе {$codeStructure->entity()->singular()} не удалось загрузить поля";
                }

                if (!preg_match('/^[a-zA-Z]+$/', $codeStructure->entity()->raw())) {
                    $errors[] = "Ресурс '{$codeStructure->entity()->raw()}' - параметр ресурса name должен содержать только латинские буквы";
                }

                foreach ($codeStructure->columns() as $column) {
                    if($column->getResourceMethods() !== []) {
                        foreach ($column->getResourceMethods() as $methodName) {
                            if(! str_contains($methodName, "(")) {
                                $errors[] = "{$column->column()} (resourceMethod - $methodName) - в методе ресурса должны быть указаны скобки, например $methodName()";
                            }
                        }
                    }

                    if($column->getMigrationMethods() !== []) {
                        foreach ($column->getMigrationMethods() as $methodName) {
                            if(! str_contains($methodName, "(")) {
                                $errors[] = "{$column->column()} (migrationMethod - $methodName) - в методе миграции должны быть указаны скобки, например $methodName()";
                            }
                        }
                    }

                    if($column->type() === SqlTypeMap::BELONGS_TO) {
                        $belongsToError = $this->checkBelongsTo($index, $column, $codeStructures->codeStructures(), $codeStructure->entity()->ucFirstSingular());
                        if($belongsToError !== '') {
                            $errors[] = $belongsToError;
                        }
                    }

                    if($column->type() === SqlTypeMap::HAS_MANY) {
                        $hasManyError = $this->checkHasMany(
                            $column,
                            $codeStructures->codeStructures()
                        );
                        if($hasManyError !== '') {
                            $errors[] = $hasManyError;
                        }
                    }

                    $typeMap = new TypeMap();
                    $field = $column->getFieldClass()
                        ? $typeMap->fieldClassFromAlias($column->getFieldClass())
                        : $typeMap->getMoonShineFieldFromSqlType($column->type())
                    ;

                    if(! class_exists($field)) {
                        $errors[] = "{$column->column()} - поля $field не существует в MoonShine";
                    }

                    if($column->getResourceMethods() !== []) {
                        foreach ($column->getResourceMethods() as $methodName) {
                            $method = strstr($methodName, '(', true);
                            try {
                                $class = new ReflectionClass($field);
                                if(! $class->hasMethod($method)) {
                                    $errors[] = "{$column->column()} - метод $method не существует для поля $field";
                                }
                            } catch (Throwable $e) {
                                $errors[] = "{$column->column()}->$methodName - ошибка проверки метода: " . $e->getMessage();
                            }
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
            if($error === 'No resources array found.') {
                $errors[] = "В схеме отсутствует основной параметр 'resources'";
            } else {
                $errors[] = $e->getMessage();
            }
        }

        if($errors === []) {
            return '';
        }

        return implode(". ", $errors);
    }

    private function checkBelongsTo(
        int $index,
        ColumnStructure $column,
        array $codeStructures,
        string $checkName
    ): string {
        $errors = [];
        $resourceName = str($column->relation()->table()->camel())->singular()->ucfirst()->value();
        foreach ($codeStructures as $checkIndex => $codeStructure) {
            if($codeStructure->entity()->ucFirstSingular() === $resourceName && $checkIndex > $index) {
                $errors[] = "Ресурс $resourceName должен быть выше $checkName в списке ресурсов";
            }
        }

        if($column->column() !== '' && ! str_contains($column->column(), '_id')) {
            $errors[] = "Поле {$column->column()} ресурса $checkName должно заканчиваться на _id для построения корректной связи";
        }

        if(
            $column->relation()->table()->raw() === 'moonshine_users'
            && $column->getModelClass() === "\\MoonShine\\Laravel\\Models\\MoonshineUser"
            && $column->column() !== 'moonshine_user_id'
        ) {
            $errors[] = "Поле {$column->column()} ресурса $checkName должно иметь значение column: moonshine_user_id";
        }

        return $errors === [] ? '' : implode(". ", $errors);
    }

    public function checkHasMany(
        ColumnStructure $column,
        array $codeStructures
    ): string {
        $errors = [];

        $relationTable = $column->relation()->table()->raw();

        $isValidTable = false;

        /** @var CodeStructure $codeStructure */
        foreach ($codeStructures as $codeStructure) {
            if($codeStructure->table() === $relationTable) {
                $isValidTable = true;
                break;
            }
        }

        if(! $isValidTable) {
            $errors[] = "Для таблицы $relationTable должен быть создан ресурс";
        }

        return $errors === [] ? '' : implode(". ", $errors);
    }
}
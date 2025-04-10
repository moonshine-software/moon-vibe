<?php

declare(strict_types=1);

namespace App\Support;

use DevLnk\MoonShineBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\MoonShineBuilder\Services\CodeStructure\RelationStructure;
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

            $tableResourceMap = $this->getTableResourceMap($codeStructures->codeStructures());

            foreach ($codeStructures->codeStructures() as $index => $codeStructure) {
                if($codeStructure->columns() === []) {
                    $errors[] = "В ресурсе {$codeStructure->entity()->singular()} не удалось загрузить поля";
                }

                if (!preg_match('/^[a-zA-Z]+$/', $codeStructure->entity()->raw())) {
                    $errors[] = "Ресурс '{$codeStructure->entity()->raw()}' - параметр ресурса name должен содержать только латинские буквы";
                }

                // PHP 8 Error
                if($codeStructure->entity()->ucFirstSingular() === 'Match') {
                    $errors[] = "Ресурс не может иметь параметр name:Match. Необходимо изменить ресурс на Game, таблицу matches изменить на games, и обновить все существующие связи.";
                }

                $relationError = $this->checkRelation(
                    $codeStructure->columns(),
                    $codeStructure->entity()->ucFirstSingular(),
                    $tableResourceMap
                );

                if($relationError !== '') {
                    $errors[] = $relationError;
                }

                $pivotError = $this->checkPivotTable($codeStructure);
                if($pivotError !== '') {
                    $errors[] = $pivotError;
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

    /**
     * @param array<int, CodeStructure> $codeStructures
     * @return array<string, string>
     */
    private function getTableResourceMap(array $codeStructures): array
    {
        $result = [];
        foreach ($codeStructures as $codeStructure) {
            $result[$codeStructure->table()] = $codeStructure->entity()->raw();
        }
        return $result;
    }

    private function checkPivotTable(CodeStructure $codeStructure): string
    {
        if(! str_contains($codeStructure->entity()->raw(), 'Pivot')) {
            return '';
        }

        $pivotTables = explode("_", $codeStructure->table());

        if(count($pivotTables) !== 2) {
            return '';
        }

        $newPivotTables = $pivotTables;

        sort($newPivotTables);

        if($newPivotTables !== $pivotTables) {
            return "Ошибка в ресурсе {$codeStructure->entity()->ucFirstSingular()}, pivot таблица задана не по конвенции laravel (таблицы в алфавитном порядке)";
        }

        return '';
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

    private function checkHasMany(
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

    /**
     * @param list<ColumnStructure> $columnStructures
     * @param string                $checkName
     * @param array                 $tableResourceMap
     *
     * @return string
     */
    private function checkRelation(array $columnStructures, string $checkName, array $tableResourceMap): string
    {
        $errors = [];

        $relationModelMethods = [];

        foreach ($columnStructures as $column) {
            if($column->relation() === null) {
                continue;
            }
            if(in_array($column->getModelRelationName(), $relationModelMethods)) {
                $errorRelationName = "Ошибка, в ресурсе $checkName у двух отношений одинаковое имя {$column->getModelRelationName()}, необходимо задать уникальное имя отношения для каждого поля, с помощью параметра model_relation_name внутри параметра relation";
                if(! in_array($errorRelationName, $errors)) {
                    $errors[] = $errorRelationName;
                }
            }

            $relationModelMethods[] = $column->getModelRelationName();

            $tableName = $column->relation()->table()->raw();
            if(
                $tableName !== 'moonshine_users'
                && ! isset($tableResourceMap[$tableName])
            ) {
                $errorTable = "Для ресурса $checkName у поля {$column->column()} задана таблица $tableName, у которой не создан ресурс";
                if($column->type() === SqlTypeMap::BELONGS_TO_MANY) {
                    $errorTable .= " Для поля BelongsToMany необходимо создать связывающий Pivot ресурс с необходимой таблицей.";
                }
                $errors[] = $errorTable;
            }
        }

        return $errors === [] ? '' : implode(". ", $errors);
    }
}
<?php

declare(strict_types=1);

namespace App\Services;

use DevLnk\MoonShineBuilder\Enums\SqlTypeMap;
use DevLnk\MoonShineBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\MoonShineBuilder\Services\CodeStructure\ColumnStructure;
use DevLnk\MoonShineBuilder\Services\CodeStructure\Factories\StructureFromArray;
use DevLnk\MoonShineBuilder\Support\TypeMap;
use ReflectionClass;
use Throwable;

class SchemaValidator
{
    public function validate(string $schema): string
    {
        $errors = [];

        try {
            if (! json_validate($schema)) {
                return 'Invalid JSON schema';
            }

            $data = json_decode($schema, true);

            $factory = new StructureFromArray($data);

            $codeStructures = $factory->makeStructures();

            if($codeStructures->codeStructures() === []) {
                return 'Failed to obtain any resources';
            }

            $tableResourceMap = $this->getTableResourceMap($codeStructures->codeStructures());

            foreach ($codeStructures->codeStructures() as $index => $codeStructure) {
                if($codeStructure->columns() === []) {
                    $errors[] = "Failed to load fields in the resource {$codeStructure->entity()->singular()}";
                }

                if (!preg_match('/^[a-zA-Z]+$/', $codeStructure->entity()->raw())) {
                    $errors[] = "Resource '{$codeStructure->entity()->raw()}' - the resource parameter name must contain only Latin letters";
                }

                // PHP 8 Error
                if($codeStructure->entity()->ucFirstSingular() === 'Match') {
                    $errors[] = "The resource cannot have the parameter name:Match. You need to change the resource to Game, rename the matches table to games, and update all existing relations.";
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
                                $errors[] = "{$column->column()} (resourceMethod - $methodName) - the resource method must include parentheses, for example $methodName()";
                            }
                        }
                    }

                    if($column->getMigrationMethods() !== []) {
                        foreach ($column->getMigrationMethods() as $methodName) {
                            if(! str_contains($methodName, "(")) {
                                $errors[] = "{$column->column()} (migrationMethod - $methodName) - the migration method must include parentheses, for example $methodName()";
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

                    /** @var class-string $field */
                    $field = $column->getFieldClass()
                        ? $typeMap->fieldClassFromAlias($column->getFieldClass())
                        : $typeMap->getMoonShineFieldFromSqlType($column->type())
                    ;

                    if(! class_exists($field)) {
                        $errors[] = "{$column->column()} - the field $field does not exist in MoonShine";
                    }

                    if($column->getResourceMethods() !== []) {
                        foreach ($column->getResourceMethods() as $methodName) {
                            /** @var string $method */
                            $method = strstr($methodName, '(', true);
                            try {
                                $class = new ReflectionClass($field);
                                if(! $class->hasMethod($method)) {
                                    $errors[] = "{$column->column()} - the method $method does not exist for the field $field";
                                }
                            } catch (Throwable $e) {
                                $errors[] = "{$column->column()}->$methodName - method validation error: " . $e->getMessage();
                            }
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
            if($error === 'No resources array found.') {
                $errors[] = "The main parameter 'resources' is missing from the schema";
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
     * @param array<array-key, CodeStructure> $codeStructures
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
            return "Error in resource {$codeStructure->entity()->ucFirstSingular()}, the pivot table is not defined according to Laravel convention (tables must be in alphabetical order)";
        }

        return '';
    }

    /**
     * @param int             $index
     * @param ColumnStructure $column
     * @param array<array-key, CodeStructure> $codeStructures
     * @param string          $checkName
     *
     * @return string
     */
    private function checkBelongsTo(
        int $index,
        ColumnStructure $column,
        array $codeStructures,
        string $checkName
    ): string {
        $errors = [];

        if($column->relation() === null) {
            return '';
        }

        $resourceName = str($column->relation()->table()->camel())->singular()->ucfirst()->value();
        foreach ($codeStructures as $checkIndex => $codeStructure) {
            if($codeStructure->entity()->ucFirstSingular() === $resourceName && $checkIndex > $index) {
                $errors[] = "Resource $resourceName must be above $checkName in the resource list";
            }
        }

        if($column->column() !== '' && ! str_contains($column->column(), '_id')) {
            $errors[] = "The field {$column->column()} of resource $checkName must end with _id to build a correct relation";
        }

        if(
            $column->relation()->table()->raw() === 'moonshine_users'
            && $column->getModelClass() === "\\MoonShine\\Laravel\\Models\\MoonshineUser"
            && $column->column() !== 'moonshine_user_id'
        ) {
            $errors[] = "The field {$column->column()} of resource $checkName must have the value column: moonshine_user_id";
        }

        return $errors === [] ? '' : implode(". ", $errors);
    }

    /**
     * @param ColumnStructure $column
     * @param array<array-key, CodeStructure> $codeStructures
     *
     * @return string
     */
    private function checkHasMany(
        ColumnStructure $column,
        array $codeStructures
    ): string {
        $errors = [];

        if($column->relation() === null) {
            return '';
        }

        $relationTable = $column->relation()->table()->raw();

        $isValidTable = false;

        foreach ($codeStructures as $codeStructure) {
            if($codeStructure->table() === $relationTable) {
                $isValidTable = true;
                break;
            }
        }

        if(! $isValidTable) {
            $errors[] = "A resource must be created for the table $relationTable";
        }

        return $errors === [] ? '' : implode(". ", $errors);
    }

    /**
     * @param array<int, ColumnStructure> $columnStructures
     * @param string                $checkName
     * @param array<string, string> $tableResourceMap
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
                $errorRelationName = "Error: in resource $checkName, two relations have the same name {$column->getModelRelationName()}; you must specify a unique relation name for each field using the model_relation_name parameter inside the relation parameter";
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
                $errorTable = "For resource $checkName, the field {$column->column()} specifies the table $tableName, for which no resource has been created";
                if($column->type() === SqlTypeMap::BELONGS_TO_MANY) {
                    $errorTable .= " For the BelongsToMany field, you need to create a linking Pivot resource with the required table.";
                }
                $errors[] = $errorTable;
            }
        }

        return $errors === [] ? '' : implode(". ", $errors);
    }
}
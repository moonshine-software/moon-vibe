<?php

declare(strict_types=1);

namespace App\Support;

use Throwable;
use ReflectionClass;
use InvalidArgumentException;
use App\Exceptions\SchemaValidationException;
use DevLnk\MoonShineBuilder\Enums\SqlTypeMap;
use DevLnk\MoonShineBuilder\Services\CodeStructure\ColumnStructure;
use DevLnk\MoonShineBuilder\Services\CodeStructure\Factories\StructureFromArray;

readonly class SchemaValidator
{
    public function __construct(
        private string $schema
    ) {
    }

    /**
     * @throws SchemaValidationException
     * @throws Throwable
     */
    public function validate(): void
    {
        try {
            if (! json_validate($this->schema)) {
                throw new InvalidArgumentException('Некорректная JSON схема');
            }

            $data = json_decode($this->schema, true);

            $factory = new StructureFromArray($data);

            $codeStructures = $factory->makeStructures();

            if($codeStructures->codeStructures() === []) {
                throw new SchemaValidationException('Не удалось получить ни одного ресурса');
            }

            $packageFields = ['Markdown', 'TinyMce'];

            foreach ($codeStructures->codeStructures() as $index => $codeStructure) {
                if($codeStructure->columns() === []) {
                    throw new SchemaValidationException("В ресурсе {$codeStructure->entity()->singular()} не удалось загрузить поля");
                }

                if (!preg_match('/^[a-zA-Z]+$/', $codeStructure->entity()->raw())) {
                    throw new SchemaValidationException("Ресурс '{$codeStructure->entity()->raw()}' - параметр ресурса name должен содержать только латинские буквы");
                }

                foreach ($codeStructure->columns() as $column) {
                    if($column->getResourceMethods() !== []) {
                        foreach ($column->getResourceMethods() as $methodName) {
                            if(! str_contains($methodName, "(")) {
                                throw new SchemaValidationException("{$column->column()} (resourceMethod - $methodName) - в методе ресурса должны быть указаны скобки, например $methodName()");
                            }
                        }
                    }

                    if($column->getMigrationMethods() !== []) {
                        foreach ($column->getMigrationMethods() as $methodName) {
                            if(! str_contains($methodName, "(")) {
                                throw new SchemaValidationException("{$column->column()} (migrationMethod - $methodName) - в методе миграции должны быть указаны скобки, например $methodName()");
                            }
                        }
                    }

                    if($column->type() === SqlTypeMap::BELONGS_TO) {
                        $this->checkBelongsToOrder($index, $column, $codeStructures->codeStructures(), $codeStructure->entity()->ucFirstSingular());
                    }

                    $field = $column->getFieldClass();
                    if($field === null && $column->getResourceMethods() !== []) {
                        throw new SchemaValidationException("{$codeStructure->entity()->singular()}({$column->column()}) - в элементе массива fields массив methods не может быть указан, если параметр field не задан");
                    }

                    if($field === null) {
                        continue;
                    }

                    if(
                        ! in_array($field, $packageFields)
                        && ! class_exists("\\MoonShine\\UI\\Fields\\$field")
                    ) {
                        throw new SchemaValidationException("{$column->column()} - поля $field не существует в MoonShine");
                    }

                    if($column->getResourceMethods() !== []) {
                        foreach ($column->getResourceMethods() as $methodName) {
                            $method = strstr($methodName, '(', true);
                            try {
                                $class = new ReflectionClass("\\MoonShine\\UI\\Fields\\$field");
                                if(! $class->hasMethod($method)) {
                                    throw new SchemaValidationException("{$column->column()} - метод $method не существует для поля $field");
                                }
                            } catch (Throwable $e) {
                                throw new SchemaValidationException("{$column->column()}->$methodName - ошибка проверки метода: " . $e->getMessage());
                            }

                        }
                    }
                }
            }
        } catch (SchemaValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            $error = $e->getMessage();
            if($error === 'No resources array found.') {
                throw new SchemaValidationException("В схеме отсутствует основной параметр 'resources'");
            }

            if(str_contains($error, "is not a valid backing value for enum DevLnk\MoonShineBuilder\Enums\SqlTypeMap")) {
                $type = str_replace(" is not a valid backing value for enum DevLnk\MoonShineBuilder\Enums\SqlTypeMap", "", $error);
                throw new SchemaValidationException("Типа $type не существует");
            }

            throw $e;
        }

    }

    /**
     * @throws SchemaValidationException
     */
    private function checkBelongsToOrder(int $index, ColumnStructure $column, array $codeStructures, string $checkName): void
    {
        $resourceName = str($column->relation()->table()->camel())->singular()->ucfirst()->value();
        foreach ($codeStructures as $checkIndex => $codeStructure) {
            if($codeStructure->entity()->ucFirstSingular() === $resourceName && $checkIndex > $index) {
                throw new SchemaValidationException("Ресурс $resourceName должен быть выше $checkName в списке ресурсов");
            }
        }
    }
}
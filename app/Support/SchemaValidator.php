<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\SchemaValidationException;
use DevLnk\MoonShineBuilder\Services\CodeStructure\Factories\StructureFromArray;
use InvalidArgumentException;
use ReflectionClass;
use Throwable;

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

            foreach ($codeStructures->codeStructures() as $codeStructure) {
                if($codeStructure->columns() === []) {
                    throw new SchemaValidationException("В ресурсе {$codeStructure->entity()->singular()} не удалось загрузить поля");
                }

                foreach ($codeStructure->columns() as $column) {
                    if($column->getResourceMethods() !== []) {
                        foreach ($column->getResourceMethods() as $methodName) {
                            if(! str_contains($methodName, "(")) {
                                throw new SchemaValidationException("{$column->column()} (resourceMethod - $methodName), в методе ресурса должны быть указаны скобки, например $methodName()");
                            }
                        }
                    }

                    if($column->getMigrationMethods() !== []) {
                        foreach ($column->getMigrationMethods() as $methodName) {
                            if(! str_contains($methodName, "(")) {
                                throw new SchemaValidationException("{$column->column()} (migrationMethod - $methodName), в методе миграции должны быть указаны скобки, например $methodName()");
                            }
                        }
                    }
                    $field = $column->getFieldClass();
                    if($field === null) {
                        continue;
                    }

                    if(
                        ! in_array($field, $packageFields)
                        && ! class_exists("\\MoonShine\\UI\\Fields\\$field")
                    ) {
                        throw new SchemaValidationException("{$column->column()}: поля $field не существует в MoonShine");
                    }

                    if($column->getResourceMethods() !== []) {
                        foreach ($column->getResourceMethods() as $methodName) {
                            $method = strstr($methodName, '(', true);
                            try {
                                $class = new ReflectionClass("\\MoonShine\\UI\\Fields\\$field");
                                if(! $class->hasMethod($method)) {
                                    throw new SchemaValidationException("{$column->column()}: метод $method не существует для поля $field");
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
            if($error === 'Undefined array key "resources"') {
                throw new SchemaValidationException("В схеме не найдены ресурсы");
            }

            if(str_contains($error, "is not a valid backing value for enum DevLnk\MoonShineBuilder\Enums\SqlTypeMap")) {
                $type = str_replace(" is not a valid backing value for enum DevLnk\MoonShineBuilder\Enums\SqlTypeMap", "", $error);
                throw new SchemaValidationException("Типа $type не существует");
            }

            report($e);
            
            throw $e;
        }

    }
}
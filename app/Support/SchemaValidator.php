<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\SchemaValidationException;
use DevLnk\MoonShineBuilder\Services\CodeStructure\Factories\StructureFromArray;
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
                    $field = $column->getFieldClass();
                    if(
                        $field !== null
                        && ! in_array($field, $packageFields)
                    ) {
                        if(! class_exists("\\MoonShine\\UI\\Fields\\$field")) {
                            throw new SchemaValidationException("{$column->column()}: поля $field не существует в MoonShine");
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

            throw $e;
        }

    }
}
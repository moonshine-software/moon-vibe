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

            foreach ($codeStructures->codeStructures() as $codeStructure) {
                if($codeStructure->columns() === []) {
                    throw new SchemaValidationException("В ресурсе {$codeStructure->entity()->singular()} не удалось загрузить поля");
                }
            }
        }catch (SchemaValidationException $e) {
            throw $e;
        } catch (Throwable) {
            throw new SchemaValidationException("Ошибка валидации схемы");
        }
    }
}
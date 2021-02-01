<?php
declare(strict_types=1);

namespace DataTransformer;

abstract class AbstractDataTransformer
{
    /**
     * @param mixed $data
     * @return array|null
     */
    abstract public function transform($data): ?array;
}

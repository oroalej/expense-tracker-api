<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;
use Tests\TestCase;

abstract class BaseGraphqlTest extends TestCase
{
    use MakesGraphQLRequests, RefreshesSchemaCache;

    public function getResponseStructure(string $queryInstance): array
    {
        $data = $this->getItemStructure();

        if (Str::plural($queryInstance) === $queryInstance) {
            $data = [
                'data' => [
                    '*' => $this->getItemStructure(),
                ],
                'paginatorInfo' => $this->getPaginationStructure(),
            ];
        }

        return [
            'data' => [
                $queryInstance => $data,
            ],
        ];
    }

    public function getStringItemStructure(): string
    {
        return implode(' ', $this->getItemStructure());
    }

    public function getStringPaginationStructure(): string
    {
        return implode(' ', $this->getPaginationStructure());
    }

    public function getPaginationStructure(): array
    {
        return [
            'count',
            'currentPage',
            'firstItem',
            'hasMorePages',
            'lastItem',
            'lastPage',
            'perPage',
            'total',
        ];
    }

    abstract public function getItemStructure(): array;
}

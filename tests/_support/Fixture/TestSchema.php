<?php
declare(strict_types=1);

namespace Tests\Fixture;

use GraphQL\Type\Schema as BaseSchema;

final class TestSchema extends BaseSchema
{
    public function __construct()
    {
        parent::__construct([]);
    }
}
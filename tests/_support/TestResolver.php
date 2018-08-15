<?php
declare(strict_types=1);

namespace Tests\Fixture;

use GraphQL\Type\Definition\ResolveInfo;

final class TestResolver
{
    public function __invoke($val, $args, $context, ResolveInfo $info)
    {
    }
}
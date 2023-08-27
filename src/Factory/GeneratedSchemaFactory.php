<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware\Factory;

use Psr\Container\ContainerInterface;
use Zestic\GraphQL\Middleware\GeneratedSchema;

class GeneratedSchemaFactory
{
    public function __invoke(ContainerInterface $container): GeneratedSchema
    {
        return new GeneratedSchema();
    }
}

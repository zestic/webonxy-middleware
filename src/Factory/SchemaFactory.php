<?php
declare(strict_types=1);

namespace Xaddax\GraphQL\Factory;

use GraphQL\Type\Schema;
use Psr\Container\ContainerInterface;

final class SchemaFactory
{
    public function __invoke(ContainerInterface $container): Schema
    {
        $config = $container->get('config')['graphQL'];
        $schemaClass = $config['schema'] ?? Schema::class;
        if (!isset($config['schemaConfig'])) {
            return new $schemaClass;
        }

        return new $schemaClass($config['schemaConfig']);
    }
}

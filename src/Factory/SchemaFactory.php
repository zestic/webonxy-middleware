<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware\Factory;

use GraphQL\Type\Schema;
use Psr\Container\ContainerInterface;

final class SchemaFactory
{
    public function __invoke(ContainerInterface $container): Schema
    {
        $config = $container->get('config')['graphQL'];
        $schemaClass = $config['schema'] ?? Schema::class;
        if ($schemaClass === 'generatedSchema') {
            return $container->get($schemaClass);
        }
        if (!isset($config['schemaConfig'])) {
            return new $schemaClass;
        }

        return new $schemaClass($config['schemaConfig']);
    }
}

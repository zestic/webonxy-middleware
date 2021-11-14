<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware\Factory;

use GraphQL\Server\ServerConfig;
use GraphQL\Type\Schema;
use Psr\Container\ContainerInterface;

final class ServerConfigFactory
{
    public function __invoke(ContainerInterface $container): ServerConfig
    {
        $containerConfig = $container->get('config');

        $config = $containerConfig['graphQL']['serverConfig'] ?? [];

        $schemaClass = $config['schema'] ?? Schema::class;
        $config['schema'] = $container->get($schemaClass);

        $callables = [
            'context',
            'errorFormatter',
            'errorsHandler',
            'fieldResolver',
            'persistentQueryLoader',
            'rootValue',
        ];
        foreach ($callables as $callableProperty) {
            if (isset($config[$callableProperty])) {
                $callable = $config[$callableProperty];
                if (!is_callable($config[$callableProperty])) {
                    $callable = $container->get($callable);
                }
                $config[$callableProperty] = $callable;
            }
        }

        return ServerConfig::create($config);
    }
}

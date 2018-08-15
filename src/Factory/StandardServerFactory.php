<?php
declare(strict_types=1);

namespace Xaddax\GraphQL\Factory;

use GraphQL\Server\StandardServer;
use GraphQL\Type\Schema;
use Psr\Container\ContainerInterface;

final class StandardServerFactory
{
    public function __invoke(ContainerInterface $container): StandardServer
    {
        $containerConfig = $container->get('config');

        $config = $containerConfig['graphQL']['server'] ?? [];

        $schemaClass = $config['schema'] ?? Schema::class;
        $config['schema'] = $container->get($schemaClass);

        $callables = [
            'errorFormatter',
            'errorsHandler',
            'fieldResolver',
            'persistentQueryLoader',
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

        return new StandardServer($config);
    }
}
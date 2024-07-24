<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware\Factory;

use GraphQL\Server\ServerConfig;
use Psr\Container\ContainerInterface;
use Zestic\GraphQL\Middleware\GraphQLMiddleware;
use Zestic\GraphQL\Middleware\RequestPreprocessorInterface;

final class GraphQLMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): GraphQLMiddleware
    {
        $containerConfig = $container->get('config');
        $config = $containerConfig['graphQL']['middleware'];
        $serverConfig = $container->get(ServerConfig::class);
        $preprocessor = $this->getPreprocessor($container, $config);

        return new GraphQLMiddleware($serverConfig, $config['allowedHeaders'], $preprocessor);
    }

    private function getPreprocessor(ContainerInterface $container, array $config): ?RequestPreprocessorInterface
    {
        return isset($config['preprocessor']) ? $container->get($config['preprocessor']) : null;
    }
}

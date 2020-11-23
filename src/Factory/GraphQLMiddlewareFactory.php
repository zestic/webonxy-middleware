<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Middleware\Factory;

use GraphQL\Server\ServerConfig;
use Psr\Container\ContainerInterface;
use IamPersistent\GraphQL\Middleware\GraphQLMiddleware;

final class GraphQLMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): GraphQLMiddleware
    {
        $serverConfig = $container->get(ServerConfig::class);
        $config = $container->get('config');

        return new GraphQLMiddleware($serverConfig, $config['graphQL']['middleware']);
    }
}

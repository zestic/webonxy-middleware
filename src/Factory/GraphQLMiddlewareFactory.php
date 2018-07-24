<?php
declare(strict_types=1);

namespace Xaddax\GraphQL\Factory;

use GraphQL\Server\StandardServer;
use Psr\Container\ContainerInterface;
use Xaddax\GraphQL\Middleware\GraphQLMiddleware;

final class GraphQLMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): GraphQLMiddleware
    {
        $server = $container->get(StandardServer::class);
        $config = $container->get('config');

        return new GraphQLMiddleware($server, $config['graphQL']['middleware']);
    }
}
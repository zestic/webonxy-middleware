<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Middleware\Factory;

use App\Jwt\JwtConfiguration;
use GraphQL\Server\ServerConfig;
use Psr\Container\ContainerInterface;
use IamPersistent\GraphQL\Middleware\GraphQLMiddleware;

final class GraphQLMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): GraphQLMiddleware
    {
        $config = $container->get('config');
        $jwtConfig = $container->get(JwtConfiguration::class);
        $serverConfig = $container->get(ServerConfig::class);

        return new GraphQLMiddleware($serverConfig, $jwtConfig, $config['graphQL']['middleware']);
    }
}

<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                \GraphQL\Type\Schema::class                              =>
                    \Zestic\GraphQL\Middleware\Factory\SchemaFactory::class,
                \GraphQL\Server\ServerConfig::class =>
                    \Zestic\GraphQL\Middleware\Factory\ServerConfigFactory::class,
                \Zestic\GraphQL\Middleware\GraphQLMiddleware::class =>
                    \Zestic\GraphQL\Middleware\Factory\GraphQLMiddlewareFactory::class,
            ],
        ];
    }
}

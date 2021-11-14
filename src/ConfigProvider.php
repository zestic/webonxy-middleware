<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Middleware;

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
                    \IamPersistent\GraphQL\Middleware\Factory\SchemaFactory::class,
                \GraphQL\Server\ServerConfig::class =>
                    \IamPersistent\GraphQL\Middleware\Factory\ServerConfigFactory::class,
                \IamPersistent\GraphQL\Middleware\GraphQLMiddleware::class =>
                    \IamPersistent\GraphQL\Middleware\Factory\GraphQLMiddlewareFactory::class,
            ],
        ];
    }
}

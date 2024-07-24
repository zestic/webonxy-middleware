<?php
declare(strict_types=1);

namespace Test\Unit\Factory;

use GraphQL\Server\ServerConfig;
use PHPUnit\Framework\TestCase;
use Test\Fixture\TestContainer;
use Zestic\GraphQL\Middleware\Factory\GraphQLMiddlewareFactory;
use Zestic\GraphQL\Middleware\GraphQLMiddleware;
use Zestic\GraphQL\Middleware\RequestPreprocessorInterface;

class GraphQLMiddlewareFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function invoke()
    {
        $preprocessor = $this->createMock(RequestPreprocessorInterface::class);
        $serverConfig = $this->createMock(ServerConfig::class);
        $config = [
            'config'                            => [
                'graphQL' => [
                    'middleware' => [
                        'allowedHeaders' => [
                            'application/json',
                        ],
                        'preprocessor'   => RequestPreprocessorInterface::class,
                    ],
                ],
            ],
            RequestPreprocessorInterface::class => $preprocessor,
            ServerConfig::class                 => $serverConfig,
        ];
        $container = new TestContainer($config);
        $middleware = (new GraphQLMiddlewareFactory())->__invoke($container);
        $this->assertInstanceOf(GraphQLMiddleware::class, $middleware);
    }

    /**
     * @test
     */
    public function invokeWithoutPreprocessor()
    {
        $serverConfig = $this->createMock(ServerConfig::class);
        $config = [
            'config'            => [
                'graphQL' => [
                    'middleware' => [
                        'allowedHeaders' => [
                            'application/json',
                        ],
                    ],
                ],
            ],
            ServerConfig::class => $serverConfig,
        ];
        $container = new TestContainer($config);
        $middleware = (new GraphQLMiddlewareFactory())->__invoke($container);
        $this->assertInstanceOf(GraphQLMiddleware::class, $middleware);
    }
}

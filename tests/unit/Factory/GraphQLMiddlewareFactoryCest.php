<?php
declare(strict_types=1);

namespace Tests\Unit\Factory;

use GraphQL\Server\StandardServer;
use IamPersistent\GraphQL\Middleware\Factory\GraphQLMiddlewareFactory;
use IamPersistent\GraphQL\Middleware\GraphQLMiddleware;
use Prophecy\Prophet;
use Psr\Container\ContainerInterface;
use UnitTester;

class GraphQLMiddlewareFactoryCest
{
    public function testInvoke(UnitTester $I)
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willImplement(ContainerInterface::class);
        $prophecy->get(StandardServer::class)->willReturn(new StandardServer([]));
        $config = [
            'graphQL' => [
                'middleware' => [
                    'allowedHeaders' => [
                        'application/json',
                    ],
                ],
            ],
        ];
        $prophecy->get('config')->willReturn($config);
        $container = $prophecy->reveal();

        $middleware = (new GraphQLMiddlewareFactory())->__invoke($container);

        $I->assertInstanceOf(GraphQLMiddleware::class, $middleware);
    }
}

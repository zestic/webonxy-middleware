<?php
declare(strict_types=1);

namespace Tests\Unit\Factory;

use GraphQL\Server\StandardServer;
use Prophecy\Prophet;
use Psr\Container\ContainerInterface;
use UnitTester;
use Xaddax\GraphQL\Factory\GraphQLMiddelwareFactory;
use Xaddax\GraphQL\Middleware\GraphQLMiddleware;

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
                'allowedHeaders' => [
                    'application/json',
                ],
            ]
        ];
        $prophecy->get('config')->willReturn($config);
        $container = $prophecy->reveal();

        $middleware = (new GraphQLMiddelwareFactory())->__invoke($container);

        $I->assertInstanceOf(GraphQLMiddleware::class, $middleware);
    }
}

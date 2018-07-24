<?php
declare(strict_types=1);

namespace Tests\Unit\Factory;

use GraphQL\Server\StandardServer;
use Prophecy\Prophet;
use Psr\Container\ContainerInterface;
use UnitTester;
use Xaddax\GraphQL\Factory\StandardServerFactory;

class StandardServerFactoryCest
{
    public function testInvoke(UnitTester $I)
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize();
        $prophecy->willImplement(ContainerInterface::class);
        $config = [
            'graphQL' => [
                'server' => [
                ],
            ],
        ];
        $prophecy->get('config')->willReturn($config);
        $container = $prophecy->reveal();

        $middleware = (new StandardServerFactory())->__invoke($container);

        $I->assertInstanceOf(StandardServer::class, $middleware);
    }
}

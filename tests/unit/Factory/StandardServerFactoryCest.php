<?php
declare(strict_types=1);

namespace Tests\Unit\Factory;

use Closure;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Schema;
use Tests\Fixture\TestContainer;
use Tests\Fixture\TestResolver;
use Tests\Fixture\TestSchema;
use UnitTester;
use Xaddax\GraphQL\Factory\StandardServerFactory;

class StandardServerFactoryCest
{
    public function testInvoke(UnitTester $I)
    {
        $config = [
            'graphQL' => [
                'server' => [
                    'fieldResolver' => TestResolver::class,
                    'schema' => TestSchema::class,
                ],
            ],
        ];
        $container = (new TestContainer())
            ->set('config', $config)
            ->set(TestSchema::class, new TestSchema())
            ->set(TestResolver::class, new TestResolver());

        $server = (new StandardServerFactory())->__invoke($container);

        $I->assertInstanceOf(StandardServer::class, $server);
        $config = $this->getServerConfig($server);
        $I->assertInstanceOf(TestSchema::class, $config->getSchema());
        $I->assertInstanceOf(TestResolver::class, $config->getFieldResolver());
    }

    public function testInvokeNonConfiguredSchema(UnitTester $I)
    {
        $container = (new TestContainer())
            ->set('config', [])
            ->set(Schema::class, new TestSchema());

        $server = (new StandardServerFactory())->__invoke($container);

        $config = $this->getServerConfig($server);
        $I->assertInstanceOf(TestSchema::class, $config->getSchema());
    }

    public function testInvokeCallable(UnitTester $I)
    {
        $errorFormatter = function() {
            return;
        };

        $config = [
            'graphQL' => [
                'server' => [
                    'errorFormatter' => $errorFormatter,
                    'fieldResolver' => TestResolver::class,
                    'schema' => TestSchema::class,
                ],
            ],
        ];
        $container = (new TestContainer())
            ->set('config', $config)
            ->set(TestSchema::class, new TestSchema())
            ->set(TestResolver::class, new TestResolver());

        $server = (new StandardServerFactory())->__invoke($container);

        $config = $this->getServerConfig($server);
        $I->assertInstanceOf(TestSchema::class, $config->getSchema());
        $I->assertInstanceOf(TestResolver::class, $config->getFieldResolver());
        $I->assertSame($errorFormatter, $config->getErrorFormatter());
    }

    private function getServerConfig(StandardServer $server): ServerConfig
    {
        $getConfig = function(StandardServer $server) {
            return $server->config;
        };

        return Closure::bind($getConfig, $server, $server)->__invoke($server);
    }
}

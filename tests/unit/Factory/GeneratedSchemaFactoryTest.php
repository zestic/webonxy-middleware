<?php
declare(strict_types=1);

namespace Test\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Test\Fixture\TestContainer;
use Zestic\GraphQL\Middleware\Factory\GeneratedSchemaFactory;

class GeneratedSchemaFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function invoke()
    {
        $config = [
            'config'                            => [
                'graphQL' => [
                    'serverConfig' => [
                        'schemaDirectories' => [
                            __DIR__. '/../../Fixture/schema',
                        ],
                    ],
                ],
            ],
        ];
        $container = new TestContainer($config);
        $schema = (new GeneratedSchemaFactory())->__invoke($container);
    }
}

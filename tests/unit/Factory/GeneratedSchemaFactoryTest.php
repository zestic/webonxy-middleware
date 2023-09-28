<?php
declare(strict_types=1);

namespace Test\Unit\Factory;

use GraphQL\Type\Schema;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Test\Fixture\TestContainer;
use Zestic\GraphQL\Middleware\Factory\GeneratedSchemaFactory;

class GeneratedSchemaFactoryTest extends TestCase
{
    const SCHEMA_CACHE_FILE = __DIR__ . '/../../Fixture/cache/schema-cache.php';
    private array $config;
    private ContainerInterface $container;
    private GeneratedSchemaFactory $factory;

    public function setUp(): void
    {
        parent::setUp();
        $this->config = [
            'config'                            => [
                'graphQL' => [
                    'serverConfig' => [
                        'schemaCacheFile' => self::SCHEMA_CACHE_FILE,
                        'schemaDirectories' => [
                            __DIR__. '/../../Fixture/schema',
                        ],
                    ],
                ],
            ],
        ];
        $this->container = new TestContainer($this->config);
        $this->factory = new GeneratedSchemaFactory();
    }

    /**
     * @test
     */
    public function invokeWithoutCache(): void
    {
        unlink(self::SCHEMA_CACHE_FILE);
        $schema = $this->factory->__invoke($this->container);
        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertTrue(file_exists(self::SCHEMA_CACHE_FILE));
    }

    /**
     * @test
     */
    public function invokeWithCache(): void
    {
        $this->assertTrue(file_exists(self::SCHEMA_CACHE_FILE));
        $startingFileTime = filemtime(self::SCHEMA_CACHE_FILE);
        $schema = $this->factory->__invoke($this->container);
        $this->assertInstanceOf(Schema::class, $schema);
        $endingFileTime = filemtime(self::SCHEMA_CACHE_FILE);
        $this->assertEquals($startingFileTime, $endingFileTime);

        unlink(self::SCHEMA_CACHE_FILE);
    }
}

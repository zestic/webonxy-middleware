<?php
declare(strict_types=1);

namespace Test\Unit\Middleware;

use GraphQL\Server\ServerConfig;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tests\Fixture\Http\TestRequestHandler;
use Zestic\GraphQL\Middleware\GraphQLMiddleware;
use Zestic\GraphQL\Middleware\RequestPreProcessorInterface;

class GraphQLMiddlewareTest extends TestCase
{
    private GraphQLMiddleware $middleware;
    private ServerConfig $serverConfig;

    public function setUp(): void
    {
        $schemaConfig = SchemaConfig::create([
            'query'    => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'hello' => [
                        'type' => Type::string(),
                        'args' => [
                            'name' => [
                                'type' => Type::string(),
                            ],
                        ],
                        'resolve' => fn ($rootValue, array $args): string => "Hello {$args['name']}",
                    ],
],
            ]),
        ]);
        $this->serverConfig = ServerConfig::create([
            'schema' => new Schema($schemaConfig),
        ]);
        $allowedHeaders = [
            'application/json',
        ];
        $this->middleware = new GraphQLMiddleware($this->serverConfig, $allowedHeaders);
    }

    public function testSkippingIfNoHeader(): void
    {
        $request = $this->createRequest([]);
        $response = $this->makeRequest($request);

        $this->assertSame('{"message":"Passed Through"}', (string) $response->getBody());
    }

    public function testSkippingIfNotGraphQLHeader(): void
    {
        $headers = [
            'content-type' => 'text/html',
        ];
        $request = $this->createRequest($headers);
        $response = $this->makeRequest($request);

        $this->assertSame('{"message":"Passed Through"}', (string) $response->getBody());
    }

    public function testSettingParsedBody(): void
    {
        $request = $this->createRequest();
        $response = $this->makeRequest($request);

        $this->assertSame('{"data":{"hello":"Hello World"}}', (string) $response->getBody());
    }

    public function testCallingRequestPreProcessorInterface(): void
    {
        $request = $this->createRequest();
        $data = [
            'query'     => 'query ping()',
        ];
        $request = $request->withParsedBody($data);
        $requestPreProcessor = $this->createMock(RequestPreProcessorInterface::class);
        $requestPreProcessor->expects($this->once())
            ->method('process')
            ->willReturn($request);
        $allowedHeaders = [
            'application/json',
        ];
        $handler = new TestRequestHandler('Test Handler');

        (new GraphQLMiddleware($this->serverConfig, $allowedHeaders, $requestPreProcessor))->process($request, $handler);
    }

    public function testPassRequestToStandardServer(): void
    {
        $headers = [
            'content-type' => 'application/json',
        ];

        $data = [
            'query'     => 'query hello($name: String!) { hello(name: $name) }',
            'variables' => [
                'name' => 'World',
            ],
        ];

        $jsonContent = json_encode($data);
        $stream = fopen('php://memory','r+');
        fwrite($stream, $jsonContent);
        rewind($stream);

        $request = new ServerRequest(
            [],
            [],
            null,
            'POST',
            $stream,
            $headers,
            [],
            [],
            $data
        );

        $allowedHeaders = [
            'application/json',
        ];
        $this->middleware = new GraphQLMiddleware($this->serverConfig, $allowedHeaders);

        $response = $this->makeRequest($request);

        $this->assertSame('{"data":{"hello":"Hello World"}}', (string) $response->getBody());
    }

    private function createRequest(?array $headers = null): ServerRequest
    {
        $headers ??= [
            'content-type' => 'application/json',
        ];
        $data = [
            'query'     => 'query hello($name: String!) { hello(name: $name) }',
            'variables' => [
                'name' => 'World',
            ],
        ];
        $jsonContent = json_encode($data);
        $stream = fopen('php://memory','r+');
        fwrite($stream, $jsonContent);
        rewind($stream);

        return new ServerRequest(
            [],
            [],
            null,
            'POST',
            $stream,
            $headers,
        );
    }

    private function makeRequest(ServerRequest $request, $handler = null): ResponseInterface
    {
        if (null === $handler) {
            $handler = new TestRequestHandler('Passed Through');
        }

        return $this->middleware->process($request, $handler);
    }
}

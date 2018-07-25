<?php
declare(strict_types=1);

namespace Test\Unit\Middleware;

use GraphQL\Server\StandardServer;
use Prophecy\Prophet;
use Psr\Http\Message\ResponseInterface;
use Tests\Fixture\Http\TestRequestHandler;
use Xaddax\GraphQL\Middleware\GraphQLMiddleware;
use UnitTester;
use Zend\Diactoros\ServerRequest;

class GraphQLMiddlewareCest
{
    /** @var GraphQLMiddleware */
    private $middleware;
    private $server;

    public function __construct()
    {
        $this->server = new StandardServer([]);
        $config = [
            'allowedHeaders' => [
                'application/json',
            ],
        ];
        $this->middleware = new GraphQLMiddleware($this->server, $config);
    }

    public function testSkippingIfNoHeader(UnitTester $I)
    {
        $request = $this->createRequest();

        $response = $this->makeRequest($request);

        $I->assertSame('{"message":"Passed Through"}', (string) $response->getBody());
    }

    public function testSkippingIfNotGraphQLHeader(UnitTester $I)
    {
        $headers = [
            'content-type' => 'text/html',
        ];
        $request = $this->createRequest([], $headers);

        $response = $this->makeRequest($request);

        $I->assertSame('{"message":"Passed Through"}', (string) $response->getBody());
    }

    public function testSettingParsedBody(UnitTester $I)
    {
        $headers = [
            'content-type' => 'application/json',
        ];
        $request = $this->createRequest([], $headers);

        $prophet = new Prophet();
        $server = $prophet->prophesize(StandardServer::class);
        $data = [
            'query'     => 'query getMatter($id: String!) {\n matter(id: $id) {\nid\n}\n}',
            'variables' => [
                'id' => '4d967a0f65224f1685a602cbe4eef667',
            ],
        ];
        $server->executePsrRequest($request->withParsedBody($data))->willReturn(['message' => 'Made It!']);

        $config = [
            'allowedHeaders' => [
                'application/json',
            ],
        ];
        $this->middleware = new GraphQLMiddleware($server->reveal(), $config);

        $response = $this->makeRequest($request);

        $I->assertSame('{"message":"Made It!"}', (string) $response->getBody());
    }

    public function testPassRequestToStandardServer(UnitTester $I)
    {
        $headers = [
            'content-type' => 'application/json',
        ];

        $data = [
            'query'     => 'query getMatter($id: String!) {\n matter(id: $id) {\nid\n}\n}',
            'variables' => [
                'id' => '4d967a0f65224f1685a602cbe4eef667',
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
            null,
            $stream,
            $headers,
            [],
            [],
            $data
        );

        $prophet = new Prophet();
        $server = $prophet->prophesize(StandardServer::class);
        $server->executePsrRequest($request)->willReturn(['message' => 'Made It!']);

        $config = [
            'allowedHeaders' => [
                'application/json',
            ],
        ];
        $this->middleware = new GraphQLMiddleware($server->reveal(), $config);

        $response = $this->makeRequest($request);

        $I->assertSame('{"message":"Made It!"}', (string) $response->getBody());
    }

    private function createRequest($data = [], $headers = []): ServerRequest
    {
        if (empty($data)) {
            $data = [
                'query'     => 'query getMatter($id: String!) {\n matter(id: $id) {\nid\n}\n}',
                'variables' => [
                    'id' => '4d967a0f65224f1685a602cbe4eef667',
                ],
            ];
        }
        $jsonContent = json_encode($data);
        $stream = fopen('php://memory','r+');
        fwrite($stream, $jsonContent);
        rewind($stream);

        return new ServerRequest(
            [],
            [],
            null,
            null,
            $stream,
            $headers
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
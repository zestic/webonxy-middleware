<?php
declare(strict_types=1);

namespace Xaddax\GraphQL\Middleware;

use GraphQL\Server\StandardServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

final class GraphQLMiddleware implements MiddlewareInterface
{
    /** @var array */
    private $allowedHeaders;
    /** @var StandardServer */
    private $graphQLServer;

    public function __construct(StandardServer $server, array $config)
    {
        $this->allowedHeaders = $config['allowedHeaders'];
        $this->graphQLServer = $server;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->hasGraphQLHeader($request)) {
            return $handler->handle($request, $handler);
        }

        if (empty($request->getParsedBody())) {
            $json = (string) $request->getBody();
            $request = $request->withParsedBody(json_decode($json, true));
        }
        $result = $this->graphQLServer->executePsrRequest($request);

        return new JsonResponse($result);
    }

    private function hasGraphQLHeader(ServerRequestInterface $request)
    {
        if (!$request->hasHeader('content-type')) {
            return false;
        }

        $requestHeaderList = array_map(
            function ($header) {
                return trim($header);
            },
            explode(",", $request->getHeaderLine("content-type"))
        );

        foreach ($this->allowedHeaders as $allowedHeader) {
            if (in_array($allowedHeader, $requestHeaderList)) {
                return true;
            }
        }

        return false;
    }
}
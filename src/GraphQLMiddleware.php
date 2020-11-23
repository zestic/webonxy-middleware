<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Middleware;

use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

final class GraphQLMiddleware implements MiddlewareInterface
{
    /** @var array */
    private $allowedHeaders;
    /** @var \GraphQL\Server\ServerConfig */
    private $serverConfig;

    public function __construct(ServerConfig $serverConfig, array $config)
    {
        $this->allowedHeaders = $config['allowedHeaders'];
        $this->serverConfig = $serverConfig;
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

        $context = $this->serverConfig->getContext();
        if ($context instanceof RequestContextInterface) {
            $context->setRequest($request);
        }
        $result = (new StandardServer($this->serverConfig))->executePsrRequest($request);

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

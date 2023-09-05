<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware;

use GraphQL\Executor\ExecutionResult;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GraphQLMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ServerConfig $serverConfig,
        private readonly array $allowedHeaders = [],
        private readonly ?RequestPreProcessorInterface $requestPreProcessor = null,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->hasGraphQLHeader($request)) {
            return $handler->handle($request);
        }

        if (empty($request->getParsedBody())) {
            $json = (string) $request->getBody();
            $request = $request->withParsedBody(json_decode($json, true));
        }

        if ($this->requestPreProcessor) {
            try {
                $request = $this->requestPreProcessor->process($request);
            } catch (\Exception $exception) {
                return new JsonResponse([
                    'errors' => [
                        'message' => $exception->getMessage(),
                    ],
                ], 401);
            }
        }

        $result = $this->executeRequest($request);

        return new JsonResponse($result);
    }

    private function executeRequest(ServerRequestInterface $request): ExecutionResult
    {
        $context = $this->serverConfig->getContext();
        if ($context instanceof RequestContextInterface) {
            $context->setRequest($request);
        }

        return (new StandardServer($this->serverConfig))->executePsrRequest($request);
    }

    private function hasGraphQLHeader(ServerRequestInterface $request): bool
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

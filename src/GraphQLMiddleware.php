<?php
declare(strict_types=1);

namespace IamPersistent\GraphQL\Middleware;

use App\Jwt\JwtConfiguration;
use Firebase\JWT\JWT;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

final class GraphQLMiddleware implements MiddlewareInterface
{
    const OPTIONS = [
        "algorithm" => ["HS256", "HS512", "HS384"],
        "header"    => "Authorization",
        "regexp"    => "/Bearer\s+(.*)$/i",
        "cookie"    => "token",
    ];

    /** @var array */
    private $allowedHeaders;
    /** @var \App\Jwt\JwtConfiguration */
    private $jwtConfig;
    /** @var array */
    private $options;
    /** @var \GraphQL\Server\ServerConfig */
    private $serverConfig;

    public function __construct(ServerConfig $serverConfig, JwtConfiguration $jwtConfig, array $config)
    {
        $this->allowedHeaders = $config['allowedHeaders'];
        $this->jwtConfig = $jwtConfig;
        $options = [
            'algorithm' => $jwtConfig->getAlgorithm(),
            'secret' => $jwtConfig->getPublicKey(),
        ];
        $this->options = array_merge(self::OPTIONS, $options);
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

        try {
            $request = $this->decodeJwtIfPresent($request);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'errors' => [
                    'message' => $exception->getMessage(),
                ]
            ], 401);
        }

        $context = $this->serverConfig->getContext();
        if ($context instanceof RequestContextInterface) {
            $context->setRequest($request);
        }
        $result = (new StandardServer($this->serverConfig))->executePsrRequest($request);

        return new JsonResponse($result);
    }

    private function decodeJwtIfPresent(ServerRequestInterface $request): ServerRequestInterface
    {
        try {
            if (!$token = $this->fetchToken($request)) {
                return $request;
            }
            $decoded = $this->decodeToken($token);
        } catch (RuntimeException | DomainException $exception) {
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }

        return $request->withAttribute('token', $decoded);
    }

    private function fetchToken(ServerRequestInterface $request): ?string
    {
        /* Check for token in header. */
        $header = $request->getHeaderLine($this->options["header"]);

        if (false === empty($header)) {
            if (preg_match($this->options["regexp"], $header, $matches)) {

                return $matches[1];
            }
        }

        /* Token not found in header try a cookie. */
        $cookieParams = $request->getCookieParams();

        if (isset($cookieParams[$this->options["cookie"]])) {
            $this->log(LogLevel::DEBUG, "Using token from cookie");
            if (preg_match($this->options["regexp"], $cookieParams[$this->options["cookie"]], $matches)) {
                return $matches[1];
            }
            return $cookieParams[$this->options["cookie"]];
        };
    }

    private function decodeToken(string $token): array
    {
        try {
            $decoded = JWT::decode(
                $token,
                $this->options["secret"],
                (array) $this->options["algorithm"]
            );
            return (array) $decoded;
        } catch (Exception $exception) {
            $this->log(LogLevel::WARNING, $exception->getMessage(), [$token]);
            throw $exception;
        }
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

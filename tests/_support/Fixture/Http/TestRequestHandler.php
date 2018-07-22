<?php
declare(strict_types=1);

namespace Tests\Fixture\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

final class TestRequestHandler implements RequestHandlerInterface
{
    /** @var ResponseInterface */
    private $response;

    public function __construct($message, $responseCode = 200)
    {
        $data = [
            'message' => $message,
        ];
        $this->response = new JsonResponse($data, $responseCode);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response;
    }
}
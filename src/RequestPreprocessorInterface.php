<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Middleware;

use Psr\Http\Message\ServerRequestInterface;

interface RequestPreprocessorInterface
{
    public function process(ServerRequestInterface $request): ServerRequestInterface;
}

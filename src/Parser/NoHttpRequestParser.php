<?php

declare(strict_types=1);

namespace Yansongda\Pay\Parser;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\ParserInterface;

class NoHttpRequestParser implements ParserInterface
{
    public function parse(?ResponseInterface $response): ?ResponseInterface
    {
        return $response;
    }
}

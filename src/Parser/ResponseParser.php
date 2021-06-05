<?php

declare(strict_types=1);

namespace Yansongda\Pay\Parser;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\PackerInterface;

class ResponseParser implements PackerInterface
{
    public function unpack(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}

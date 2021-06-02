<?php

declare(strict_types=1);

namespace Yansongda\Pay\Packer;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\PackerInterface;

class ResponsePacker implements PackerInterface
{
    public function unpack(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}

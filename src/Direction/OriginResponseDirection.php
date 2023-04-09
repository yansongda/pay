<?php

declare(strict_types=1);

namespace Yansongda\Pay\Direction;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Contract\ParserInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;

class OriginResponseDirection implements ParserInterface
{
    /**
     * @throws InvalidResponseException
     */
    public function parse(PackerInterface $packer, ?ResponseInterface $response): ?ResponseInterface
    {
        if (!is_null($response)) {
            return $response;
        }

        throw new InvalidResponseException(Exception::INVALID_RESPONSE_CODE);
    }
}

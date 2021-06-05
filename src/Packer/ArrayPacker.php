<?php

declare(strict_types=1);

namespace Yansongda\Pay\Packer;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Exception\InvalidResponseException;

class ArrayPacker implements PackerInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     */
    public function unpack(ResponseInterface $response): array
    {
        $contents = $response->getBody()->getContents();

        $result = json_decode($contents, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidResponseException(InvalidResponseException::UNPACK_RESPONSE_ERROR, 'Unpack Response Error', [$contents]);
        }

        return $result;
    }
}

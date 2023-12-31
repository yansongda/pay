<?php

declare(strict_types=1);

namespace Yansongda\Pay\Direction;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;

class OriginResponseDirection implements DirectionInterface
{
    /**
     * @throws InvalidResponseException
     */
    public function guide(PackerInterface $packer, ?ResponseInterface $response): ?ResponseInterface
    {
        if (is_null($response)) {
            throw new InvalidResponseException(Exception::RESPONSE_EMPTY, '响应异常: 响应为空，不能进行 direction');
        }

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace Yansongda\Pay\Direction;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Supports\Collection;

class CollectionDirection implements DirectionInterface
{
    /**
     * @throws InvalidResponseException
     */
    public function guide(PackerInterface $packer, ?ResponseInterface $response): Collection
    {
        if (is_null($response)) {
            throw new InvalidResponseException(Exception::RESPONSE_EMPTY, '响应异常: 响应为空，不能进行 direction');
        }

        $body = (string) $response->getBody();

        if (!is_null($result = $packer->unpack($body))) {
            return new Collection($result);
        }

        throw new InvalidResponseException(Exception::RESPONSE_UNPACK_ERROR, '响应异常: 解包错误', ['body' => $body, 'response' => $response]);
    }
}

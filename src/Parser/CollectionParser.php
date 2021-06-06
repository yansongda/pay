<?php

declare(strict_types=1);

namespace Yansongda\Pay\Parser;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class CollectionParser implements PackerInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function unpack(ResponseInterface $response): Collection
    {
        return new Collection(
            Pay::get(JsonParser::class)->unpack($response)
        );
    }
}

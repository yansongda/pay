<?php

declare(strict_types=1);

namespace Yansongda\Pay\Direction;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;

class CollectionDirection implements DirectionInterface
{
    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function parse(PackerInterface $packer, ?ResponseInterface $response): Collection
    {
        return new Collection(
            Pay::get(ArrayDirection::class)->parse($packer, $response)
        );
    }
}

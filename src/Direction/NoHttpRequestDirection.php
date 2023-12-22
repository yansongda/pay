<?php

declare(strict_types=1);

namespace Yansongda\Pay\Direction;

use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Contract\PackerInterface;

class NoHttpRequestDirection implements DirectionInterface
{
    public function guide(PackerInterface $packer, ?ResponseInterface $response): ?ResponseInterface
    {
        return $response;
    }
}

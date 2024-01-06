<?php

declare(strict_types=1);

namespace Yansongda\Pay\Packer;

use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;

class QueryPacker implements PackerInterface
{
    public function pack(null|array|Collection $payload): string
    {
        return Collection::wrap($payload)->query();
    }

    public function unpack(string $payload): array
    {
        return Arr::wrapQuery($payload, true);
    }
}

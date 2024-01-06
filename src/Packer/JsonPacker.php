<?php

declare(strict_types=1);

namespace Yansongda\Pay\Packer;

use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;

class JsonPacker implements PackerInterface
{
    public function pack(null|array|Collection $payload): string
    {
        if (empty($payload)) {
            return '';
        }

        return Collection::wrap($payload)->toJson();
    }

    public function unpack(string $payload): ?array
    {
        return Arr::wrapJson($payload);
    }
}

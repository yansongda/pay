<?php

declare(strict_types=1);

namespace Yansongda\Pay\Packer;

use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Supports\Str;

class QueryPacker implements PackerInterface
{
    public function pack(array $payload): string
    {
        return http_build_query($payload, '', '&');
    }

    public function unpack(string $payload): ?array
    {
        if (empty($payload) || !Str::contains($payload, '=')) {
            return [];
        }

        $result = [];

        foreach (explode('&', $payload) as $item) {
            $pos = strpos($item, '=');

            $result[substr($item, 0, $pos)] = substr($item, $pos + 1);
        }

        return $result;
    }
}

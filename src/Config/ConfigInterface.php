<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

interface ConfigInterface
{
    /**
     * Convert config entity to array.
     */
    public function toArray(): array;

    /**
     * Create config entity from array.
     */
    public static function fromArray(array $config): self;
}

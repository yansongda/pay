<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

interface ConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function set(string $key, mixed $value): void;
}

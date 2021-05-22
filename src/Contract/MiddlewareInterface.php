<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Yansongda\Supports\Collection;

interface MiddlewareInterface
{
    public function apply(array $payload): Collection;
}

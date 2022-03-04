<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

interface ServiceProviderInterface
{
    public function register(?array $data = null): void;
}

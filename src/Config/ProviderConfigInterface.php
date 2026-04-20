<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

interface ProviderConfigInterface
{
    public function getTenant(): string;

    public function getMode(): int;
}

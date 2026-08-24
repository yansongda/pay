<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

interface ProviderConfigInterface
{
    public function getTenant(): string;

    public function getMode(): int;

    public function get(?string $key = null, mixed $default = null): mixed;

    /**
     * 校验配置完整性，缺少必要配置参数时抛出异常.
     */
    public function validate(): void;
}

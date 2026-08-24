<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Supports\Str;
use Yansongda\Supports\Traits\Accessable;
use Yansongda\Supports\Traits\Arrayable;
use Yansongda\Supports\Traits\Serializable;

abstract class AbstractConfig implements ProviderConfigInterface
{
    use Accessable;
    use Arrayable;
    use Serializable;

    protected string $tenant;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values, string $tenant = 'default')
    {
        $this->tenant = $tenant;
        $this->unserializeArray($values);
        $this->validateRequired();
    }

    public function getTenant(): string
    {
        return $this->tenant;
    }

    abstract public function getMode(): int;

    abstract protected function validateRequired(): void;

    /**
     * @param array<int, string> $props
     *
     * @throws InvalidConfigException 缺少必要配置参数
     */
    protected function validateNotEmpty(array $props, int $exceptionCode, string $messagePrefix): void
    {
        foreach ($props as $prop) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    $exceptionCode,
                    $messagePrefix.' -- ['.Str::snake($prop).']'
                );
            }
        }
    }
}

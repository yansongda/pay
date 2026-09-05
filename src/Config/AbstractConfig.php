<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Contract\ProviderConfigInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
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
    }

    public function getTenant(): string
    {
        return $this->tenant;
    }

    /**
     * @throws InvalidConfigException 缺少必要配置参数或配置值不合法
     */
    public function validate(): void
    {
        $this->validateMode();
        $this->validateRequired();
    }

    abstract public function getMode(): int;

    abstract protected function validateRequired(): void;

    /**
     * 支持的 mode 集合，无特殊需求的 Provider 无需覆盖.
     *
     * @return array<int, int>
     */
    protected function supportedModes(): array
    {
        return [Pay::MODE_NORMAL, Pay::MODE_SANDBOX, Pay::MODE_SERVICE];
    }

    /**
     * @throws InvalidConfigException mode 配置值不合法
     */
    protected function validateMode(): void
    {
        if (!in_array($this->getMode(), $this->supportedModes(), true)) {
            throw new InvalidConfigException(Exception::CONFIG_PROVIDER_INVALID, '配置异常: [mode] 配置不合法');
        }
    }

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

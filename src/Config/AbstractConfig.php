<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Contract\ProviderConfigInterface;
use Yansongda\Pay\Enum\PayMode;
use Yansongda\Pay\Exception\Exception;
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
     * 运行模式：内部以 int 存储（兼容历史 `0`/`1`/`2` 写法）；对外 `getMode()` 返回 `PayMode`，
     * `toArray()['mode']` 经 getter 同步返回枚举，`toJson()` / `json_encode()` 仍输出 int。
     */
    protected int $mode = PayMode::NORMAL->value;

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

    public function getMode(): PayMode
    {
        return PayMode::fromLoose($this->mode);
    }

    /**
     * 设置运行模式，兼容历史 int 写法（`0`/`1`/`2`）.
     */
    public function setMode(int|PayMode $value): void
    {
        $this->mode = $value instanceof PayMode ? $value->value : $value;
    }

    abstract protected function validateRequired(): void;

    /**
     * 支持的 mode 集合，无特殊需求的 Provider 无需覆盖.
     *
     * @return array<int, PayMode>
     */
    protected function supportedModes(): array
    {
        return [PayMode::NORMAL, PayMode::SANDBOX, PayMode::SERVICE];
    }

    /**
     * @throws InvalidConfigException mode 配置值不合法
     */
    protected function validateMode(): void
    {
        // 注意：这里校验原始 int 值，不能用 getMode()——后者对非法值会回落 NORMAL，会漏掉非法 mode
        $mode = PayMode::tryFrom($this->mode);

        if (is_null($mode) || !in_array($mode, $this->supportedModes(), true)) {
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

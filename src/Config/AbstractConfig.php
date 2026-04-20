<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Supports\Traits\Accessable;
use Yansongda\Supports\Traits\Arrayable;
use Yansongda\Supports\Traits\Serializable;

abstract class AbstractConfig implements ProviderConfigInterface
{
    use Accessable;
    use Arrayable;
    use Serializable;

    protected string $tenant;

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
}

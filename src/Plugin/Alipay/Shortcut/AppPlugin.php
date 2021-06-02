<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Shortcut;

use Closure;
use Yansongda\Pay\Plugin\Alipay\Trade\AppPayPlugin;
use Yansongda\Supports\Collection;

class AppPlugin extends AppPayPlugin
{
    public function assembly(array $params, Collection $payload, Closure $next): Collection
    {
        return parent::assembly(...func_get_args());
    }
}

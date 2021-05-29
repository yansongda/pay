<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Shortcut;

use Closure;
use Yansongda\Pay\Plugin\Alipay\Trade\PagePayPlugin;
use Yansongda\Supports\Collection;

class WebPlugin extends PagePayPlugin
{
    public function apply(array $params, Collection $payload, Closure $next): Collection
    {
        return parent::apply(...func_get_args());
    }
}

<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Closure;
use Yansongda\Pay\Rocket;

interface PluginInterface
{
    /**
     * @author yansongda <me@yansongda.cn>
     *
     * @return \Yansongda\Supports\Collection|\Symfony\Component\HttpFoundation\Response
     */
    public function assembly(Rocket $rocket, Closure $next);
}

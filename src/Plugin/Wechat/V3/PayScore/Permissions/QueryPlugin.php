<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012647401
 */
class QueryPlugin implements PluginInterface
{
    use WechatTrait;

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][PayScore][Permissions][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

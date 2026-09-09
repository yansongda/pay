<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\PayScore;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012647427
 */
class ModifyPlugin implements PluginInterface
{
    use WechatTrait;

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][PayScore][ModifyPlugin] 插件开始装载', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

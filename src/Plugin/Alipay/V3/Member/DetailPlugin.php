<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Member;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://github.com/alipay/alipay-sdk-php-all/blob/master/v3/docs/Api/AlipayUserInfoApi.md
 */
class DetailPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Member][DetailPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $authToken = $params['auth_token'] ?? $params['_auth_token'] ?? '';

        $rocket->mergePayload([
            '_method' => 'POST',
            '_url' => '/v3/alipay/user/info/share?auth_token='.urlencode($authToken),
            '_body' => '',
        ]);

        Logger::info('[Alipay][V3][Member][DetailPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

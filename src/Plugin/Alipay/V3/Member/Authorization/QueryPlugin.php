<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3\Member\Authorization;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;

/**
 * @see https://github.com/alipay/alipay-sdk-php-all/blob/master/v3/docs/Api/AlipayOpenAuthUserauthRelationshipApi.md
 */
class QueryPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][Member][Authorization][QueryPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            '_method' => 'GET',
            '_url' => '/v3/alipay/open/auth/userauth/relationship/query?'.http_build_query($rocket->getParams()),
            '_body' => '',
        ]);

        Logger::info('[Alipay][V3][Member][Authorization][QueryPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

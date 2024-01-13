<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Artful\filter_params;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/fapiao/fapiao-applications/reverse-fapiao-applications.html
 */
class ReversePlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Fapiao][Blockchain][ReversePlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $applyId = $payload?->get('fapiao_apply_id') ?? null;

        if (empty($applyId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 冲红电子发票，参数缺少 `fapiao_apply_id`');
        }

        $rocket->setPayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/new-tax-control-fapiao/fapiao-applications/'.$applyId.'/reverse',
            ],
            filter_params($payload)->except('fapiao_apply_id')->all(),
        ));

        Logger::info('[Wechat][V3][Marketing][Fapiao][Blockchain][ReversePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

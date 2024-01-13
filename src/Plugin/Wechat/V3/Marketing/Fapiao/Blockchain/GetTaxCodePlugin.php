<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/fapiao/fapiao-merchant/list-merchant-tax-codes.html
 */
class GetTaxCodePlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Fapiao][Blockchain][GetTaxCodePlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $offset = $payload?->get('offset') ?? null;
        $limit = $payload?->get('limit') ?? null;

        if (empty($offset) || empty($limit)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 获取商户可开具的商品和服务税收分类编码对照表，缺少 `offset` 或 `limit` 参数');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/new-tax-control-fapiao/merchant/tax-codes?offset='.$offset.'&limit='.$limit,
        ]);

        Logger::info('[Wechat][V3][Marketing][Fapiao][Blockchain][GetTaxCodePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

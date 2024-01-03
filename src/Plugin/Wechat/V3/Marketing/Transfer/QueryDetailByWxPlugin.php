<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/transfer-detail/get-transfer-detail-by-no.html
 */
class QueryDetailByWxPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][QueryDetailByWxPlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();
        $batchId = $payload?->get('batch_id') ?? null;
        $detailId = $payload?->get('detail_id') ?? null;

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: 通过微信明细单号查询明细单，只支持普通商户模式，当前配置为服务商模式');
        }

        if (empty($batchId) || empty($detailId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 通过微信明细单号查询明细单，参数缺少 `batch_id` 或 `detail_id`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/transfer/batches/batch-id/'.$batchId.'/details/detail-id/'.$detailId,
        ]);

        Logger::info('[Wechat][Marketing][Transfer][QueryDetailByWxPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

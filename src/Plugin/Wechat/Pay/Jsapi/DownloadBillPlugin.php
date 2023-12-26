<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Jsapi;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/jsapi-payment/download-bill.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/partner-jsapi-payment/download-bill.html
 */
class DownloadBillPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Jsapi][DownloadBillPlugin] 插件开始装载', ['rocket' => $rocket]);

        $downloadUrl = $rocket->getPayload()->get('download_url');

        if (empty($downloadUrl)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: Jsapi 下载交易对账单，参数缺少 `download_url`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => $downloadUrl,
            '_service_url' => $downloadUrl,
        ]);

        Logger::info('[Wechat][Pay][Jsapi][DownloadBillPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

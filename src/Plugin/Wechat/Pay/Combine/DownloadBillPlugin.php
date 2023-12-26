<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Combine;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/combine-payment/bill-download/download-bill.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/combine-payment/bill-download/download-bill.html
 */
class DownloadBillPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Combine][DownloadBillPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $downloadUrl = $payload?->get('download_url') ?? null;

        if (empty($downloadUrl)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 合单 下载交易对账单，参数缺少 `download_url`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => $downloadUrl,
            '_service_url' => $downloadUrl,
        ]);

        Logger::info('[Wechat][Pay][Combine][DownloadBillPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Marketing\Transfer;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/batch-transfer-to-balance/download-receipt.html
 */
class DownloadReceiptPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Marketing][Transfer][DownloadReceiptPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (!$payload?->has('download_url')) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 下载电子回单，参数缺少 `download_url`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => $payload->get('download_url'),
        ]);

        Logger::info('[Wechat][Marketing][Transfer][DownloadReceiptPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

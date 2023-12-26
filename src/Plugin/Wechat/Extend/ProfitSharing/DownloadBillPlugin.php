<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Extend\ProfitSharing;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/download-bill.html
 */
class DownloadBillPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][ProfitSharing][DownloadBillPlugin] 插件开始装载', ['rocket' => $rocket]);

        $downloadUrl = $rocket->getPayload()?->get('download_url') ?? null;

        if (empty($downloadUrl)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 下载电子回单，参数缺少 `download_url`');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => $downloadUrl,
        ]);

        Logger::info('[Wechat][Extend][ProfitSharing][DownloadBillPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

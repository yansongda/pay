<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\OriginResponseDirection;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Artful\filter_params;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/fapiao/fapiao-applications/download-invoice-file.html
 */
class DownloadPlugin implements PluginInterface
{
    /**
     * @throws InvalidParamsException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Fapiao][Blockchain][DownloadPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();
        $downloadUrl = $payload?->get('download_url') ?? null;

        if (empty($downloadUrl)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 下载发票文件，缺少 `download_url` 参数');
        }

        $rocket->setDirection(OriginResponseDirection::class)
            ->setPayload([
                '_method' => 'GET',
                '_url' => $downloadUrl.'&'.filter_params($payload)->except('download_url')->query(),
            ]);

        Logger::info('[Wechat][V3][Marketing][Fapiao][Blockchain][DownloadPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Open;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;
use function Yansongda\Pay\get_unipay_config;

class AddPayloadSignaturePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][AddPayloadSignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_unipay_config($params);
        $payload = $rocket->getPayload();

        if (empty($payload) || $payload->isEmpty()) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 银联支付必要参数缺失。可能插件用错顺序，应该先使用 `业务插件`');
        }

        $rocket->mergePayload([
            'signature' => $this->getSignature($config['certs']['pkey'] ?? '', filter_params($rocket->getPayload(), fn ($k, $v) => 'signature' != $k)),
        ]);

        Logger::info('[Unipay][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function getSignature(string $pkey, Collection $payload): string
    {
        if (empty($pkey)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 银联支付配置文件中未找到 `certs.pkey` 配置项。可能插件用错顺序，应该先使用 `StartPlugin`');
        }

        $content = $payload->sortKeys()->toString();

        openssl_sign(hash('sha256', $content), $sign, $pkey, 'sha256');

        return base64_encode($sign);
    }
}

<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Qra;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\UnipayTrait;

use function Yansongda\Artful\filter_params;

class AddPayloadSignaturePlugin implements PluginInterface
{
    use UnipayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     * @throws InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Unipay][Qra][AddPayloadSignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var UnipayConfig $config */
        $config = self::getProviderConfig('unipay', $params);
        $payload = $rocket->getPayload();

        if (empty($payload) || $payload->isEmpty()) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 银联支付必要参数缺失。可能插件用错顺序，应该先使用 `业务插件`');
        }

        $rocket->mergePayload(['sign' => self::getUnipaySignQra($config, filter_params($payload)->all())]);

        Logger::info('[Unipay][Qra][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}

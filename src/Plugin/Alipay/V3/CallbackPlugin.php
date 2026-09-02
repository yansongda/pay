<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\V3;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\AlipayTrait;

use function Yansongda\Artful\filter_params;

class CallbackPlugin implements PluginInterface
{
    use AlipayTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     * @throws InvalidSignException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Alipay][V3][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->init($rocket);

        $params = $rocket->getParams();

        /** @var AlipayConfig $config */
        $config = self::getProviderConfig('alipay', $params);

        // 支付宝 trade 异步通知为 V2 form 参数格式（非 V3 header 签名格式）：
        // 除去 `sign`/`sign_type` 后按字典序组串 + RSA2 验签；不校验 SN、无毫秒时间戳环节、无条件强制
        $request = $rocket->getDestinationOrigin();
        $formParams = [];

        if ($request instanceof ServerRequestInterface) {
            $parsedBody = $request->getParsedBody();
            $formParams = is_array($parsedBody) ? $parsedBody : [];
        }

        $value = filter_params($formParams, fn ($k, $v) => '' !== $v && 'sign' != $k && 'sign_type' != $k);

        self::verifyAlipayV3Sign($config, $value->sortKeys()->toString(), $formParams['sign'] ?? '');

        // 验签通过：完整 form 参数（含 sign/sign_type，保持通知原样）设为 payload 与 destination
        $rocket->setPayload($formParams)
            ->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[Alipay][V3][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function init(Rocket $rocket): void
    {
        $request = $rocket->getParams()['_request'] ?? null;
        $params = $rocket->getParams()['_params'] ?? [];

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 支付宝回调参数不正确');
        }

        $rocket->setDestination(clone $request)
            ->setDestinationOrigin($request)
            ->setParams($params);
    }
}

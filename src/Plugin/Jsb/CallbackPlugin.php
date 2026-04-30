<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Jsb;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Traits\JsbTrait;
use Yansongda\Supports\Collection;

class CallbackPlugin implements PluginInterface
{
    use JsbTrait;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     * @throws InvalidSignException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[Jsb][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->formatRequestAndParams($rocket);

        $params = $rocket->getParams();

        /** @var JsbConfig $config */
        $config = self::getProviderConfig('jsb', $params);

        $payload = $rocket->getPayload();
        $signature = $payload->get('sign');

        $payload->forget('sign');
        $payload->forget('signType');

        self::verifyJsbSign($config, $payload->sortKeys()->toString(), $signature);

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[Jsb][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws InvalidParamsException
     */
    protected function formatRequestAndParams(Rocket $rocket): void
    {
        $request = $rocket->getParams()['request'] ?? null;

        if (!$request instanceof Collection) {
            throw new InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID);
        }

        $rocket->setPayload($request)->setParams($rocket->getParams()['params'] ?? []);
    }
}

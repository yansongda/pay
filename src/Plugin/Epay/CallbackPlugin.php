<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Direction\NoHttpRequestDirection;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\get_provider_config;

class CallbackPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[epay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->formatRequestAndParams($rocket);

        $params = $rocket->getParams();
        $config = get_provider_config('epay', $params);

        $payload = $rocket->getPayload();
        $signature = $payload->get('sign');

        $payload->forget('sign');
        $payload->forget('signType');

        $this->verifySign($config, $payload, $signature);

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[epay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function verifySign(array $config, Collection $payload, ?string $signature = null): void
    {
        if (!$signature) {
            throw new InvalidResponseException(Exception::SIGN_ERROR, 'Verify Epay payload Sign Failed: sign is empty', $payload);
        }

        $publicCert = $config['epay_public_cert_path'] ?? null;

        if (empty($publicCert)) {
            throw new InvalidConfigException(Exception::CONFIG_EPAY_INVALID, 'Missing Epay Config -- [epay_public_cert_path]');
        }

        $result = 1 === openssl_verify(
            $payload->sortKeys()->toString(),
            base64_decode($signature),
            file_get_contents($publicCert)
        );
        if (!$result) {
            throw new InvalidConfigException(Exception::SIGN_ERROR, 'Verify Epay Response Sign Failed', func_get_args());
        }
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

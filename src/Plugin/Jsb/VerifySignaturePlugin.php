<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Jsb;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

use function Yansongda\Artful\should_do_http_request;
use function Yansongda\Pay\get_provider_config;
use function Yansongda\Pay\verify_jsb_sign;

class VerifySignaturePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::info('[Jsb][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (should_do_http_request($rocket->getDirection())) {
            $params = $rocket->getParams();
            $config = get_provider_config('jsb', $params);

            $body = (string) $rocket->getDestinationOrigin()->getBody();
            $signatureData = $this->getSignatureData($body);

            verify_jsb_sign($config, $signatureData['data'] ?? '', $signatureData['sign'] ?? '');
        }

        Logger::info('[Jsb][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    private function getSignatureData(string $body): array
    {
        if (Str::contains($body, '&-&')) {
            $beginIndex = strpos($body, '&signType=');
            $endIndex = strpos($body, '&-&');
            $data = substr($body, 0, $beginIndex).substr($body, $endIndex);

            $signIndex = strpos($body, '&sign=');
            $signature = substr($body, $signIndex + strlen('&sign='), $endIndex - ($signIndex + strlen('&sign=')));
        } else {
            $result = Arr::wrapQuery($body, true);
            $result = Collection::wrap($result);
            $signature = $result->get('sign');
            $result->forget('sign');
            $result->forget('signType');
            $data = $result->sortKeys()->toString();
        }

        return [
            'sign' => $signature,
            'data' => $data,
        ];
    }
}

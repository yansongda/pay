<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

use function Yansongda\Artful\should_do_http_request;
use function Yansongda\Pay\get_provider_config;

class VerifySignaturePlugin implements PluginInterface
{
    /**
     * @throws ServiceNotFoundException
     * @throws ContainerException|InvalidSignException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::info('[Epay][VerifySignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        if (should_do_http_request($rocket->getDirection())) {
            $params = $rocket->getParams();
            $config = get_provider_config('epay', $params);
            $body = (string) $rocket->getDestinationOrigin()->getBody();
            $this->verifySign($config, $body);
        }

        Logger::info('[Epay][VerifySignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @throws InvalidSignException
     */
    protected function verifySign(array $config, string $body): void
    {
        // 解析签名
        $signatureData = $this->getSignatureData($body);

        if (!$signatureData['sign']) {
            throw new InvalidSignException(Exception::RESPONSE_MISSING_NECESSARY_PARAMS, 'Verify Epay Response Sign Failed: sign is empty', $body);
        }

        $publicCert = $config['epay_public_cert_path'] ?? null;
        if (empty($publicCert)) {
            throw new InvalidSignException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, 'Missing Epay Config -- [epay_public_cert_path]');
        }
        $result = 1 === openssl_verify(
            $signatureData['data'],
            base64_decode($signatureData['sign']),
            file_get_contents($publicCert)
        );
        if (!$result) {
            throw new InvalidSignException(Exception::SIGN_ERROR, 'Verify Epay Response Sign Failed', func_get_args());
        }
    }

    protected function query(string $body): array
    {
        $result = [];
        foreach (explode('&', $body) as $item) {
            $pos = strpos($item, '=');
            if (!$pos) {
                continue;
            }
            $result[substr($item, 0, $pos)] = substr($item, $pos + 1);
        }

        return $result;
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

<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Douyin\V1\Pay;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;
use function Yansongda\Pay\get_provider_config;

class AddPayloadSignaturePlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Douyin][V1][Pay][AddPayloadSignaturePlugin] 插件开始装载', ['rocket' => $rocket]);

        $config = get_provider_config('douyin', $rocket->getParams());
        $payload = $rocket->getPayload();

        $rocket->mergePayload(['sign' => $this->getSign($config, filter_params($payload))]);

        Logger::info('[Douyin][V1][Pay][AddPayloadSignaturePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws InvalidConfigException
     */
    protected function getSign(array $config, Collection $payload): string
    {
        $salt = $config['mch_secret_salt'] ?? null;

        if (empty($salt)) {
            throw new InvalidConfigException(Exception::CONFIG_DOUYIN_INVALID, '配置异常: 缺少抖音配置 -- [mch_secret_salt]');
        }

        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if (in_array($key, ['other_settle_params', 'app_id', 'sign', 'thirdparty_id']) || empty($value) || 'null' === $value) {
                continue;
            }

            if (is_array($value)) {
                $value = $this->arrayToString($value);
            }

            $signData[] = $value;
        }

        $signData[] = $salt;

        sort($signData, SORT_STRING);

        return md5(implode('&', $signData));
    }

    protected function arrayToString(array $value): string
    {
        $isJsonArray = isset($value[0]);
        $keys = array_keys($value);

        if ($isJsonArray) {
            sort($keys);
        }

        foreach ($keys as $key) {
            $val = $value[$key];

            $result[] = is_array($val) ? $this->arrayToString($val) : (($isJsonArray ? '' : $key.':').trim(strval($val)));
        }

        $result = '['.implode(' ', $result ?? []).']';

        return ($isJsonArray ? '' : 'map').$result;
    }
}

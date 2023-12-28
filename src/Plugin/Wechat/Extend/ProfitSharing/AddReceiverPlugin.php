<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Extend\ProfitSharing;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\encrypt_wechat_contents;
use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_public_key;
use function Yansongda\Pay\get_wechat_serial_no;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/receivers/add-receiver.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/receivers/add-receiver.html
 */
class AddReceiverPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][ProfitSharing][AddReceiverPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $config = get_wechat_config($params);
        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少分账参数');
        }

        if (Pay::MODE_SERVICE === ($config['mode'] ?? Pay::MODE_NORMAL)) {
            $data = $this->service($payload, $params, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/profitsharing/receivers/add',
                '_service_url' => 'v3/profitsharing/receivers/add',
            ],
            $data ?? $this->normal($payload, $params, $config),
        ));

        Logger::info('[Wechat][Extend][ProfitSharing][AddReceiverPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function normal(Collection $payload, array $params, array $config): array
    {
        $data = [
            'appid' => $payload->get('appid', $config[get_wechat_type_key($params)] ?? ''),
        ];

        if (!$payload->has('name')) {
            return $data;
        }

        return array_merge($data, $this->encryptSensitiveData($params, $config, $payload));
    }

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function service(Collection $payload, array $params, array $config): array
    {
        $data = [
            'sub_mchid' => $payload->get('sub_mchid', $config['sub_mch_id'] ?? ''),
            'appid' => $payload->get('appid', $config[get_wechat_type_key($params)] ?? ''),
        ];

        if ('PERSONAL_SUB_OPENID' === $payload->get('type')) {
            $data['sub_appid'] = $payload->get('sub_appid', $config['sub_'.get_wechat_type_key($params)] ?? '');
        }

        if (!$payload->has('name')) {
            return $data;
        }

        return array_merge($data, $this->encryptSensitiveData($params, $config, $payload));
    }

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function encryptSensitiveData(array $params, array $config, Collection $payload): array
    {
        $data['_serial_no'] = get_wechat_serial_no($params);
        $publicKey = get_wechat_public_key($config, $data['_serial_no']);

        $data['name'] = encrypt_wechat_contents($payload->get('name'), $publicKey);

        return $data;
    }
}

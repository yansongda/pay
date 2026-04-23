<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/receivers/add-receiver.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/receivers/add-receiver.html
 */
class AddReceiverPlugin implements PluginInterface
{
    use WechatTrait;

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

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少添加分账接收方参数');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
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
    protected function normal(Collection $payload, array $params, WechatConfig $config): array
    {
        $data = [
            'appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
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
    protected function service(Collection $payload, array $params, WechatConfig $config): array
    {
        $data = [
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? ''),
            'appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
        ];

        if ('PERSONAL_SUB_OPENID' === $payload->get('type')) {
            $data['sub_appid'] = $config->getSubAppIdByType($params['_type'] ?? 'mp') ?? '';
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
    protected function encryptSensitiveData(array $params, WechatConfig $config, Collection $payload): array
    {
        $data['_serial_no'] = self::getWechatSerialNo($params);

        $publicKey = self::getWechatPublicKey($config, $data['_serial_no']);

        $data['name'] = self::encryptWechatContents($payload->get('name'), $publicKey);

        return $data;
    }
}

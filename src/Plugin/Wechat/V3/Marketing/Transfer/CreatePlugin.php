<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer;

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
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012716434
 */
class CreatePlugin implements PluginInterface
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
        Logger::debug('[Wechat][Marketing][Transfer][CreatePlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);

        if (Pay::MODE_SERVICE === $config->getMode()) {
            throw new InvalidParamsException(Exception::PARAMS_PLUGIN_ONLY_SUPPORT_NORMAL_MODE, '参数异常: 发起商家转账，只支持普通商户模式，当前配置为服务商模式');
        }

        if (is_null($payload) || $payload->isEmpty()) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 发起商家转账参数，参数缺失');
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/fund-app/mch-transfer/transfer-bills',
                'appid' => $payload->get('appid', $config->getAppIdByType($params['_type'] ?? 'mp') ?? ''),
                'notify_url' => $payload->get('notify_url', $config->getNotifyUrl()),
            ],
            $this->normal($params, $payload)
        ));

        Logger::info('[Wechat][Marketing][Transfer][CreatePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     * @throws DecryptException
     * @throws InvalidConfigException
     */
    protected function normal(array $params, Collection $payload): array
    {
        if (!$payload->has('user_name')) {
            return [];
        }

        return $this->encryptSensitiveData($params, $payload);
    }

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function encryptSensitiveData(array $params, Collection $payload): array
    {
        $data['_serial_no'] = self::getWechatSerialNo($params);

        $config = self::getProviderConfig('wechat', $params);
        $publicKey = self::getWechatPublicKey($config, $data['_serial_no']);

        $data['user_name'] = self::encryptWechatContents($payload->get('user_name'), $publicKey);

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Virtual\Goods;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Traits\WechatTrait;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html
 */
class StartPublishGoodsPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Virtual][Goods][StartPublishGoodsPlugin] 插件开始装载', ['rocket' => $rocket]);

        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 微信虚拟支付启动发布道具，参数为空');
        }

        $env = (int) $payload->get('env', 0);

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => '/xpay/start_publish_goods',
                '_env' => $env,
                'group_id' => $payload->get('group_id'),
                'upload_task_id' => $payload->get('upload_task_id'),
                'env' => $env,
            ],
            $this->getAccessToken($payload),
        ));

        Logger::info('[Wechat][Virtual][Goods][StartPublishGoodsPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getAccessToken($payload): array
    {
        $token = $payload->get('_access_token', '');

        if (!empty($token)) {
            return ['_access_token' => $token];
        }

        return [];
    }
}

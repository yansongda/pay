<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Mini;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_5_1.shtml
 */
class PrepayPlugin extends \Yansongda\Pay\Plugin\Wechat\Pay\Common\PrepayPlugin
{
    protected function getWechatId(array $config, Rocket $rocket): array
    {
        $wechatId = parent::getWechatId($config, $rocket);

        if (Pay::MODE_SERVICE === ($config['mode'] ?? null)
            && empty($wechatId['sp_appid'])) {
            $wechatId['sp_appid'] = $config['mp_app_id'] ?? '';
        }

        return $wechatId;
    }

    protected function getConfigKey(array $params): string
    {
        return 'mini_app_id';
    }
}

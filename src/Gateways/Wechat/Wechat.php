<?php 

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Support\Config;
use Yansongda\Pay\Traits\HasHttpRequest;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;

/**
* 
*/
class Wechat
{
    use HasHttpRequest;

    /**
     * [$config description]
     * @var [type]
     */
    protected $config;

    /**
     * [$user_config description]
     * @var [type]
     */
    protected $user_config;

    /**
     * [__construct description]
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-14
     * @param   array      $config [description]
     */
    public function __construct(array $config)
    {
        $this->user_config = new Config($config);
    }

    /**
     * 生成随机字符串
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-14
     * @param   integer    $length [description]
     * @return  [type]             [description]
     */
    protected function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }
}

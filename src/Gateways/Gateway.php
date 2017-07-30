<?php 

namespace Yansongda\Pay\Gateways;

use Yansongda\Pay\Support\Config;
use Yansongda\Pay\Contracts\GatewayInterface;

/**
*   
*/
abstract class Gateway implements GatewayInterface
{
    /**
     * 抽象出的公共接口
     * @var [type]
     */
    abstract protected $public_config;

    /**
     * 业务参数
     * @var [type]
     */
    abstract protected $biz_config;
    
    /**
     * [__construct description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @param   array      $config [description]
     */
    public function __construct(array $config = [])
    {
        $this->public_config = new Config(array_merge($this->public_config, $config));
    }

    abstract protected function getPayUrl();

    /**
     * 支付
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @return  [type]     [description]
     */
    public function pay($biz_config, $type = 'web')
    {
        
    }

    /**
     * 退款
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @return  [type]     [description]
     */
    abstract public function refund();

    /**
     * 关闭
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @return  [type]     [description]
     */
    abstract public function close();

    /**
     * 验证
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @return  [type]     [description]
     */
    abstract public function verify();

    /**
     * 签名
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @return  [type]     [description]
     */
    abstract protected function sign();
}
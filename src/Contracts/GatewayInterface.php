<?php 
namespace Yansongda\Pay\Contracts;

/**
 * Interface GatewayInterface
 */
interface GatewayInterface
{
    /**
     * 支付
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @return  [type]     [description]
     */
    public function pay()
    
    /**
     * 验证
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @return  [type]     [description]
     */
    public function verify()
}
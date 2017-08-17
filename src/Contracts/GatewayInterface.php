<?php
namespace Yansongda\Pay\Contracts;

/**
 * Interface GatewayInterface.
 */
interface GatewayInterface
{
    /**
     * 支付
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-15
     * 
     * @param   array      $config_biz [description]
     * 
     * @return  mixed                  [description]
     */
    public function pay(array $config_biz);

    /**
     * 退款
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-15
     * 
     * @param   array      $config_biz [description]
     * 
     * @return  boolean                [description]
     */
    public function refund(array $config_biz);

    /**
     * 关闭
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-15
     * 
     * @param   array      $config_biz [description]
     * 
     * @return  boolean                [description]
     */
    public function close(array $config_biz);

    /**
     * 验证消息是否官方发出
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-15
     * 
     * @param   mixed      $data 待签名数组
     * @param   string     $sign 签名字符串-支付宝服务器发送过来的原始串
     * @param   bool       $sync 是否同步验证
     * 
     * @return  boolean          [description]
     */
    public function verify($data, $sign = null, $sync = false);
}

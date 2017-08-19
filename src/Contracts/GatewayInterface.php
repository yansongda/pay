<?php

namespace Yansongda\Pay\Contracts;

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
     * @param   array|string     $config_biz [description]
     * 
     * @return  array|boolean                [description]
     */
    public function refund($config_biz);

    /**
     * 关闭
     * 
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-15
     * 
     * @param   array|string $config_biz [description]
     * 
     * @return  array|boolean                  [description]
     */
    public function close($config_biz);

    /**
     * 对外接口 - 订单查询
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-19
     * 
     * @param   string     $out_trade_no 商家订单号
     * 
     * @return  array|boolean            [description]
     */
    public function find($out_trade_no);

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
     * @return  array|boolean          [description]
     */
    public function verify($data, $sign = null, $sync = false);
}

<?php 
namespace Yansongda\Pay\Contracts;

/**
 * Interface GatewayInterface
 */
interface GatewayInterface
{
    /**
     * 支付
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-11
     * @return  [type]     [description]
     */
    public function pay(array $config_biz);

    /**
     * 退款
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-11
     * @return  [type]     [description]
     */
    public function refund(array $config_biz);

    /**
     * 关闭
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-11
     * @return  [type]     [description]
     */
    public function close(array $config_biz);

    /**
     * 验证消息是否官方发出
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-11
     * @return  [type]     [description]
     */
    public function verify(array $data, $sign);
}

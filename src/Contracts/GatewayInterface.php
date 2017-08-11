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
    public function pay();

    /**
     * 退款
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-11
     * @return  [type]     [description]
     */
    public function refund();

    /**
     * 关闭
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-11
     * @return  [type]     [description]
     */
    public function close();

    /**
     * 验证消息是否官方发出
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-11
     * @return  [type]     [description]
     */
    public function verify(array $data, $sign);
}

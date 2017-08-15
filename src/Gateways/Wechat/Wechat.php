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
abstract class Wechat implements GatewayInterface
{
    use HasHttpRequest;

    /**
     * [$preOrder_gateway description]
     * @var string
     */
    protected $preOrder_gateway = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

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

        $this->config = [
            'appid' => $this->user_config->get('app_id'),
            'mch_id' => $this->user_config->get('mch_id'),
            'nonce_str' => $this->createNonceStr(),
            'sign_type' => 'MD5',
            'notify_url' => $this->user_config->get('notify_url'),
            'trade_type' => $this->getTradeType(),
        ];
    }

    /**
     * 对外支付接口
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-15
     * @param   array      $cofnig_biz [description]
     * @return  [type]                 [description]
     */
    abstract public function pay(array $config_biz);

    /**
     * 预下单
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-15
     * @return  [type]     [description]
     */
    protected function preOrder()
    {
        $data = $this->fromXml($this->get($this->preOrder_gateway, $this->config));

        if ($data['return_code'] !== 'SUCCESS' || $data['result_code'] !== 'SUCCESS') {
            throw new GatewayException(
                'preOrder error:' . $data['return_msg'] . ' - ' . $data['err_code_des'],
                20000,
                $data);
        }

        if ($this->getSign($res) !== $data['sign']) {
            throw new GatewayException(
                'preOrder error: return data sign error',
                20000,
                $data);
        }

        return $data;
    }

    /**
     * 签名
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-15
     * @param   array      $data 带签名数组
     * @return  string           [description]
     */
    protected function getSign($data)
    {
        if (is_null($this->user_config->get('key'))) {
            throw new InvalidArgumentException("Missing Config -- [key]");
        }

        ksort($data);

        $string = md5($this->getSignContent($data) . "&key=" . $this->user_config->get('key'));

        return strtoupper($string);
    }

    /**
     * 将数组转换成 URL 格式
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-15
     * @param   array      $data [description]
     * @return  string           [description]
     */
    protected function getSignContent($data)
    {
        $buff = "";

        foreach ($data as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        
        return trim($buff, "&");
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

    /**
     * 转化为 xml
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-14
     * @param   array      $data 带转化数组
     * @return  string           转化后的xml字符串
     */
    protected function toXml($data)
    {
        if(!is_array($data) || count($data) <= 0){
            throw new InvalidArgumentException("convert to xml error!invalid array!");
        }
        
        $xml = "<xml>";
        foreach ($data as $key => $val){
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";

        return $xml; 
    }

    /**
     * xml 转化为 array
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-14
     * @param   string     $xml xml字符串
     * @return  array           转化后的数组
     */
    protected function fromXml($xml)
    {   
        if(!$xml){
            throw new InvalidArgumentException("convert to array error !invalid xml");
        }

        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);        
    }
}

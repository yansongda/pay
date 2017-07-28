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
     * [$config description]
     * @var [type]
     */
    protected $config;
    
    /**
     * [__construct description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @param   array      $config [description]
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    /**
     * 获取配置
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @return  [type]     [description]
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 设置配置
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @param   Config     $config [description]
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }
}
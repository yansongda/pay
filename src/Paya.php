<?php 

namespace Yansongda\Pay;

use Yansongda\Pay\Support\Config;
use Yansongda\Pay\Exceptions\InvalidArgumentException;

/**
 * Pay class
 * ==========================
 * 配置选项：
 * $config = [
 *     'alipay' => [
 *         'app_id' => '',
 *         'notify' => '',
 *         'return' => '',
 *         'ali_public_key' => '',
 *         'private_key' => '',
 *     ],
 *
 *     'wechat' => [
 *         'appid' => '',
 *         'mch_id' => '',
 *         'notify_url' => '',
 *     ],
 * ]
 * @var [type]
 */
class Pay
{
    /**
     * [$config description]
     * @var [type]
     */
    private $config;

    /**
     * [__construct description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @param   array      $config [description]
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
    }

    /**
     * [driver description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $driver [description]
     * @return  [type]             [description]
     */
    public function driver($driver)
    {
        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * [createDriver description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $driver [description]
     * @return  [type]             [description]
     */
    private function createDriver($driver)
    {
        if (file_exists(__DIR__ . '/Gateways/' . ucfirst($driver) . 'Gateway.php')) {
            $gateway = __NAMESPACE__ . '\\Gateways\\' . ucfirst($driver) . 'Gateway';

            return $this->buildGateway($gateway, $this->config->get($driver));
        }

        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    /**
     * [buildGateway description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $gateway [description]
     * @param   [type]     $config  [description]
     * @return  [type]              [description]
     */
    private function buildGateway($gateway, $config)
    {
        return new $gateway($config);
    }
}
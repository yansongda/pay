<?php 

namespace Yansongda\Pay;

use Yansongda\Pay\Support\Config;
use Yansongda\Pay\Exceptions\InvalidArgumentException;

/**
 * 
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
        if (file_exists(__DIR__ . '/Gateways/' . ucfirst($driver) . '.php') ||
            ! is_null($this->config->get($driver))) {

            return $this->buildDriver(
                __NAMESPACE__ . '\\Gateways\\' . ucfirst($driver),
                $this->config->get($driver));
        }

        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    /**
     * [buildDriver description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $gateway [description]
     * @param   [type]     $config  [description]
     * @return  [type]              [description]
     */
    private function buildDriver($driver, $config)
    {
        return new $driver($config);
    }
}
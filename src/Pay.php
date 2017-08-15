<?php

namespace Yansongda\Pay;

use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Support\Config;

class Pay
{
    /**
     * [$config description].
     *
     * @var [type]
     */
    private $config;

    /**
     * [$dirvers description].
     *
     * @var [type]
     */
    private $dirvers;

    /**
     * [$gateways description].
     *
     * @var [type]
     */
    private $gateways;

    /**
     * [__construct description].
     *
     * @author JasonYan <me@yansongda.cn>
     *
     * @version 2017-07-29
     *
     * @param array $config [description]
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
    }

    /**
     * [driver description].
     *
     * @author JasonYan <me@yansongda.cn>
     *
     * @version 2017-07-30
     *
     * @param [type] $driver [description]
     *
     * @return [type] [description]
     */
    public function driver($driver)
    {
        if (is_null($this->config->get($driver))) {
            throw new InvalidArgumentException("Driver [$driver]'s Config is not defined.");
        }

        $this->drivers = $driver;

        return $this;
    }

    /**
     * [gateway description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-10
     *
     * @param string $gateway [description]
     *
     * @return [type] [description]
     */
    public function gateway($gateway = 'web')
    {
        if (!isset($this->drivers)) {
            throw new InvalidArgumentException('Driver is not defined.');
        }

        $this->gateways = $this->createGateway($gateway);

        return $this->gateways;
    }

    /**
     * [createGateway description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-10
     *
     * @param [type] $gateway [description]
     *
     * @return [type] [description]
     */
    private function createGateway($gateway)
    {
        if (!file_exists(__DIR__.'/Gateways/'.ucfirst($this->drivers).'/'.ucfirst($gateway).'Gateway.php')) {
            throw new InvalidArgumentException("Gateway [$gateway] is not supported.");
        }

        $gateway = __NAMESPACE__.'\\Gateways\\'.ucfirst($this->drivers).'\\'.ucfirst($gateway).'Gateway';

        return $this->build($gateway);
    }

    /**
     * [buildDriver description].
     *
     * @author JasonYan <me@yansongda.cn>
     *
     * @version 2017-07-30
     *
     * @param [type] $driver [description]
     * @param [type] $config [description]
     *
     * @return [type] [description]
     */
    private function build($gateway)
    {
        return new $gateway($this->config->get($this->drivers));
    }
}

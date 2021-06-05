<h1 style="text-align: center;">Pay</h1>

<p style="text-align: center;">

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yansongda/pay/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yansongda/pay/?branch=master)
[![Linter Status](https://github.com/yansongda/pay/workflows/Linter/badge.svg)](https://github.com/yansongda/pay/actions)
[![Tester Status](https://github.com/yansongda/pay/workflows/Tester/badge.svg)](https://github.com/yansongda/pay/actions)
[![Latest Stable Version](https://poser.pugx.org/yansongda/pay/v/stable)](https://packagist.org/packages/yansongda/pay)
[![Total Downloads](https://poser.pugx.org/yansongda/pay/downloads)](https://packagist.org/packages/yansongda/pay)
[![Latest Unstable Version](https://poser.pugx.org/yansongda/pay/v/unstable)](https://packagist.org/packages/yansongda/pay)
[![License](https://poser.pugx.org/yansongda/pay/license)](https://packagist.org/packages/yansongda/pay)

</p>

**当前 master 分支为正在开发的 v3 版本，如果提交 PR 请提交到 v2 分支**

该文档为 v2.x 版本，如果您想找 v1.x 版本文档，请点击[https://github.com/yansongda/pay/tree/v1](https://github.com/yansongda/pay/tree/v1)

**注意：v1.x 与 v2.x 版本不兼容**

开发了多次支付宝与微信支付后，很自然产生一种反感，惰性又来了，想在网上找相关的轮子，可是一直没有找到一款自己觉得逞心如意的，要么使用起来太难理解，要么文件结构太杂乱，只有自己撸起袖子干了。

**！！请先熟悉 支付宝/微信 说明文档！！请具有基本的 debug 能力！！**

欢迎 Star，欢迎 PR！

laravel 扩展包请 [传送至这里](https://github.com/yansongda/laravel-pay)
yii 扩展包请 [传送至这里](https://github.com/guanguans/yii-pay)

QQ交流群：690027516

## 特点
- 丰富的事件系统
- 命名不那么乱七八糟
- 隐藏开发者不需要关注的细节
- 根据支付宝、微信最新 API 开发而成
- 高度抽象的类，免去各种拼json与xml的痛苦
- 符合 PSR 标准，你可以各种方便的与你的框架集成
- 文件结构清晰易理解，可以随心所欲添加本项目中没有的支付网关
- 方法使用更优雅，不必再去研究那些奇怪的的方法名或者类名是做啥用的


## 运行环境
- PHP 7.3+
- composer

## 支持的支付方法
### 1、支付宝
- 电脑支付
- 手机网站支付
- APP 支付
- 刷卡支付
- 扫码支付
- 账户转账
- 小程序支付

|  method   |   描述       |
| :-------: | :-------:   |
|  web      | 电脑支付     |
|  wap      | 手机网站支付 |
|  app      | APP 支付    |
|  pos      | 付款码刷卡支付  |
|  scan     | 扫码支付  |
|  transfer | 帐户转账  |
|  mini     | 小程序支付 |

### 2、微信
- 公众号支付
- 小程序支付
- H5 支付
- 扫码支付
- 刷卡支付
- APP 支付
- 企业付款
- 普通红包
- 分裂红包

| method |   描述     |
| :-----: | :-------: |
| mp      | 公众号支付  |
| miniapp | 小程序支付  |
| wap     | H5 支付    |
| scan    | 扫码支付    |
| pos     | 刷卡支付    |
| app     | APP 支付  |
| transfer     | 企业付款 |
| redpack      | 普通红包 |
| groupRedpack | 分裂红包 |

## 安装
```shell
composer require yansongda/pay -vvv
```

## 使用说明

### 支付宝
```php
<?php

namespace App\Http\Controllers;

use Yansongda\Pay\Pay;

class PayController
{
    protected $config = [
        'alipay' => [
            'default' => [
                'app_id' => '2016082000295641',
                // 加密方式： **RSA2**; 公钥证书模式 
                'app_secret_cert' => 'MIIEpAIBAAKCAQEAs6fsdafasfasfsafsafasfasfas',
                //应用公钥证书路径
                'app_public_cert_path' => '/User/yansongda/cert/app_public.crt',
                //应用公钥证书路径
                'alipay_root_cert_path' => '/User/yansongda/cert/alipay_root.crt',
                'notify_url' => 'https://yansongda.cn/notify.html',
                'return_url' => 'https://yansongda.cn/return.html',
                'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
            ],       
        ],   
        'log' => [ // optional
            'file' => './logs/alipay.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
    ];

    public function index()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '1',
            'subject' => 'test subject - 测试',
        ];

        $alipay = Pay::alipay($this->config)->web($order);

        return $alipay->send();// laravel 框架中请直接 `return $alipay`
    }

    public function return()
    {
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！

        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
    }

    public function notify()
    {
        $alipay = Pay::alipay($this->config);
    
        try{
            $data = $alipay->verify(); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
        } catch (\Exception $e) {
            // $e->getMessage();
        }

        return $alipay->success()->send();// laravel 框架中请直接 `return $alipay->success()`
    }
}
```

### 微信
```php
<?php

namespace App\Http\Controllers;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;

class PayController
{
    protected $config = [
        'appid' => 'wxb3fxxxxxxxxxxx', // APP APPID
        'app_id' => 'wxb3fxxxxxxxxxxx', // 公众号 APPID
        'miniapp_id' => 'wxb3fxxxxxxxxxxx', // 小程序 APPID
        'mch_id' => '14577xxxx',
        'key' => 'mF2suE9sU6Mk1Cxxxxxxxxxxx',
        'notify_url' => 'http://yanda.net.cn/notify.php',
        'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
        'cert_key' => './cert/apiclient_key.pem',// optional，退款等情况时用到
        'log' => [ // optional
            'file' => './logs/wechat.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'dev', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
    ];

    public function index()
    {
        $order = [
            'out_trade_no' => time(),
            'total_fee' => '1', // **单位：分**
            'body' => 'test body - 测试',
            'openid' => 'onkVf1FjWS5SBIixxxxxxx',
        ];

        $pay = Pay::wechat($this->config)->mp($order);

        // $pay->appId
        // $pay->timeStamp
        // $pay->nonceStr
        // $pay->package
        // $pay->signType
    }

    public function notify()
    {
        $pay = Pay::wechat($this->config);

        try{
            $data = $pay->verify(); // 是的，验签就这么简单！

            Log::debug('Wechat notify', $data->all());
        } catch (\Exception $e) {
            // $e->getMessage();
        }
        
        return $pay->success()->send();// laravel 框架中请直接 `return $pay->success()`
    }
}
```

## 事件系统
[请见详细文档](http://pay.yansongda.cn)

## 详细文档
[详细说明文档](http://pay.yansongda.cn)

## 错误
如果在调用相关支付网关 API 时有错误产生，会抛出 `GatewayException`,`InvalidSignException` 错误，可以通过 `$e->getMessage()` 查看，同时，也可通过 `$e->raw` 查看调用 API 后返回的原始数据，该值为数组格式。

### 所有异常

* Yansongda\Pay\Exceptions\InvalidGatewayException ，表示使用了除本 SDK 支持的支付网关。
* Yansongda\Pay\Exceptions\InvalidSignException ，表示验签失败。
* Yansongda\Pay\Exceptions\InvalidConfigException ，表示缺少配置参数，如，`ali_public_key`, `private_key` 等。
* Yansongda\Pay\Exceptions\GatewayException ，表示支付宝/微信服务器返回的数据非正常结果，例如，参数错误，对账单不存在等。


## 代码贡献
由于测试及使用环境的限制，本项目中只开发了「支付宝」和「微信支付」的相关支付网关。

如果您有其它支付网关的需求，或者发现本项目中需要改进的代码，**_欢迎 Fork 并提交 PR！_**

## 赏一杯咖啡吧

![pay](https://pay.yansongda.cn/images/pay.jpg)

## LICENSE
MIT

<p align="center">
      <a href="https://pay.yansongda.cn" target="_blank" rel="noopener noreferrer"><img width="200" src="https://cdn.jsdelivr.net/gh/yansongda/pay-site/.vuepress/public/images/logo.png" alt="Logo"></a>
</p>

<p align="center">
    <a href="https://scrutinizer-ci.com/g/yansongda/pay/?branch=master"><img src="https://scrutinizer-ci.com/g/yansongda/pay/badges/quality-score.png?b=master" alt="scrutinizer"></a>
    <a href="https://github.com/yansongda/pay/actions"><img src="https://github.com/yansongda/pay/workflows/Linter/badge.svg" alt="Linter Status"></a>
    <a href="https://github.com/yansongda/pay/actions"><img src="https://github.com/yansongda/pay/workflows/Tester/badge.svg" alt="Tester Status"></a>
    <a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/v/stable" alt="Stable Version"></a>
    <a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/downloads" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/license" alt="License"></a>
</p>

## 前言

v3 版与 v2 版在底层有很大的不同，基础架构做了重新的设计，更易扩展，使用起来更方便。

开发了多次支付宝与微信支付后，很自然产生一种反感，惰性又来了，想在网上找相关的轮子，可是一直没有找到一款自己觉得逞心如意的，要么使用起来太难理解，要么文件结构太杂乱，只有自己撸起袖子干了。

欢迎 Star，欢迎 PR！

hyperf 扩展包请 [传送至这里](https://github.com/yansongda/hyperf-pay)

laravel 扩展包请 [传送至这里](https://github.com/yansongda/laravel-pay)

yii 扩展包请 [传送至这里](https://github.com/guanguans/yii-pay)

## 特点

- 多租户支持
- Swoole 支持
- 灵活的插件机制
- 丰富的事件系统
- 命名不那么乱七八糟
- 隐藏开发者不需要关注的细节
- 根据支付宝、微信最新 API 开发而成
- 高度抽象的类，免去各种拼json与xml的痛苦
- 文件结构清晰易理解，可以随心所欲添加本项目中没有的支付网关
- 方法使用更优雅，不必再去研究那些奇怪的的方法名或者类名是做啥用的
- 内置自动获取微信公共证书方法，再也不用再费劲去考虑第一次获取证书的的问题了
- 符合 PSR2、PSR3、PSR4、PSR7、PSR11、PSR14 等各项标准，你可以各种方便的与你的框架集成

## 运行环境
- PHP 7.3+
- composer

## 详细文档

[https://pay.yansongda.cn](https://pay.yansongda.cn)

## 支持的支付方法

yansongda/pay 100% 兼容 支付宝/微信 所有功能（包括服务商功能），只需通过「插件机制」引入即可。

同时，SDK 直接支持内置了以下插件，详情请查阅文档。

### 支付宝

- 电脑支付
- 手机网站支付
- APP 支付
- 刷卡支付
- 扫码支付
- 账户转账
- 小程序支付
- ...

### 微信

- 公众号支付
- 小程序支付
- H5 支付
- 扫码支付
- APP 支付
- ...
- ~~刷卡支付，微信v3版暂不支持，计划后续内置支持v2版，或直接使用 Pay v2 版本~~
- ~~普通红包，微信v3版暂不支持，计划后续内置支持v2版，或直接使用 Pay v2 版本~~
- ~~分裂红包，微信v3版暂不支持，计划后续内置支持v2版，或直接使用 Pay v2 版本~~

## 安装
```shell
composer require yansongda/pay:~3.0.0 -vvv
```

## 深情一撇

### 支付宝
```php
<?php

namespace App\Http\Controllers;

use Yansongda\Pay\Pay;

class AlipayController
{
    protected $config = [
        'alipay' => [
            'default' => [
                // 必填-支付宝分配的 app_id
                'app_id' => '2016082000295641',
                // 必填-应用私钥 字符串或路径
                'app_secret_cert' => '89iZ2iC16H6/6a3YcP+hDZUjiNGQx9cuwi9eJyykvcwhD...',
                // 必填-应用公钥证书 路径
                'app_public_cert_path' => '/Users/yansongda/pay/cert/appCertPublicKey_2016082000295641.crt',
                // 必填-支付宝公钥证书 路径
                'alipay_public_cert_path' => '/Users/yansongda/pay/cert/alipayCertPublicKey_RSA2.crt',
                // 必填-支付宝根证书 路径
                'alipay_root_cert_path' => '/Users/yansongda/pay/cert/alipayRootCert.crt',
                'return_url' => 'https://yansongda.cn/alipay/return',
                'notify_url' => 'https://yansongda.cn/alipay/notify',
                // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
                'service_provider_id' => '',
                // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
                'mode' => Pay::MODE_NORMAL,
            ],       
        ],   
        'logger' => [ // optional
            'enable' => false,
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

    public function web()
    {
        $result = Pay::alipay($this->config)->web([
            'out_trade_no' => ''.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 1',
        ]);
        
        return $result;
    }

    public function returnCallback()
    {
        $data = Pay::alipay($this->config)->callback(); // 是的，验签就这么简单！

        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
    }

    public function notifyCallback()
    {
        $alipay = Pay::alipay($this->config);
    
        try{
            $data = $alipay->callback(); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
        } catch (\Exception $e) {
            // $e->getMessage();
        }

        return $alipay->success();
    }
}
```

### 微信
```php
<?php

namespace App\Http\Controllers;

use Yansongda\Pay\Pay;

class WechatController
{
    protected $config = [
        'wechat' => [
            'default' => [
                // 必填-商户号，服务商模式下为服务商商户号
                'mch_id' => '',
                // 必填-商户秘钥
                'mch_secret_key' => '',
                // 必填-商户私钥 字符串或路径
                'mch_secret_cert' => '',
                // 必填-商户公钥证书路径
                'mch_public_cert_path' => '',
                // 必填
                'notify_url' => 'https://yansongda.cn/wechat/notify',
                // 选填-公众号 的 app_id
                'mp_app_id' => '2016082000291234',
                // 选填-小程序 的 app_id
                'mini_app_id' => '',
                // 选填-app 的 app_id
                'app_id' => '',
                // 选填-合单 app_id
                'combine_app_id' => '',
                // 选填-合单商户号 
                'combine_mch_id' => '',
                // 选填-服务商模式下，子公众号 的 app_id
                'sub_mp_app_id' => '',
                // 选填-服务商模式下，子 app 的 app_id
                'sub_app_id' => '',
                // 选填-服务商模式下，子小程序 的 app_id
                'sub_mini_app_id' => '',
                // 选填-服务商模式下，子商户id
                'sub_mch_id' => '',
                // 选填-微信公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
                'wechat_public_cert_path' => [
                    '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
                ],
                // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
                'mode' => Pay::MODE_NORMAL,
            ]
        ],
        'logger' => [ // optional
            'enable' => false,
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
    ];

    public function index()
    {
        $order = [
            'out_trade_no' => time().'',
            'description' => 'subject-测试',
            'amount' => [
                 'total' => 1,
            ],
            'payer' => [
                 'openid' => 'onkVf1FjWS5SBxxxxxxxx',
            ],
        ];

        $pay = Pay::wechat($this->config)->mp($order);

        // $pay->appId
        // $pay->timeStamp
        // $pay->nonceStr
        // $pay->package
        // $pay->signType
    }

    public function notifyCallback()
    {
        $pay = Pay::wechat($this->config);

        try{
            $data = $pay->callback(); // 是的，验签就这么简单！
        } catch (\Exception $e) {
            // $e->getMessage();
        }
        
        return $pay->success();
    }
}
```

## 代码贡献

由于测试及使用环境的限制，本项目中只开发了「支付宝」和「微信支付」的相关支付网关。

如果您有其它支付网关的需求，或者发现本项目中需要改进的代码，**_欢迎 Fork 并提交 PR！_**

## 赏一杯咖啡吧

![pay](https://cdn.jsdelivr.net/gh/yansongda/pay-site/.vuepress/public/images/pay.jpg)

## LICENSE

MIT

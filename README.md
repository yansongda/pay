<p align="center">
      <a href="https://pay.yansongda.cn" target="_blank" rel="noopener noreferrer"><img width="200" src="https://cdn.jsdelivr.net/gh/yansongda/pay/web/public/images/logo.png" alt="Logo"></a>
</p>

<p align="center">
    <a href="https://codecov.io/gh/yansongda/pay" ><img src="https://codecov.io/gh/yansongda/pay/branch/master/graph/badge.svg?token=tYMV0YT5jj"/></a>
    <a href="https://scrutinizer-ci.com/g/yansongda/pay/?branch=master"><img src="https://scrutinizer-ci.com/g/yansongda/pay/badges/quality-score.png?b=master" alt="scrutinizer"></a>
    <a href="https://github.com/yansongda/pay/actions"><img src="https://github.com/yansongda/pay/workflows/Tester/badge.svg" alt="Tester Status"></a>
    <a href="https://github.com/yansongda/pay/actions"><img src="https://github.com/yansongda/pay/workflows/Code%20Coverage/badge.svg" alt="Code Coverage Status"></a>
    <a href="https://github.com/yansongda/pay/actions"><img src="https://github.com/yansongda/pay/workflows/Coding%20Style/badge.svg" alt="Coding Style Status"></a>
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
- 符合 PSR2、PSR3、PSR4、PSR7、PSR11、PSR14、PSR18 等各项标准，你可以各种方便的与你的框架集成

## 版本计划

[https://pay.yansongda.cn/docs/v3/overview/planning](https://pay.yansongda.cn/docs/v3/overview/planning)

## 详细文档

[https://pay.yansongda.cn](https://pay.yansongda.cn)

## 支持的支付方法

yansongda/pay 100% 兼容 支付宝/微信/银联 所有功能（包括服务商功能），只需通过「插件机制」引入即可。

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
- 刷卡支付
- ...

### 抖音

- 小程序支付
- ...

### 银联

- 手机网站支付
- 电脑网站支付
- 刷卡支付
- 扫码支付
- ...
- 
### 江苏银行(e融支付)

- 聚合扫码支付(微信,支付宝,银联,e融)
- ...

## 安装
```shell
composer require yansongda/pay:~3.7.0 -vvv
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
                // 选填-第三方应用授权token
                'app_auth_token' => '',
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
        Pay::config($this->config);
        
        $result = Pay::alipay()->web([
            'out_trade_no' => ''.time(),
            'total_amount' => '0.01',
            'subject' => 'yansongda 测试 - 1',
        ]);
        
        return $result;
    }

    public function returnCallback()
    {
        Pay::config($this->config);
    
        $data = Pay::alipay()->callback(); // 是的，验签就这么简单！

        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
    }

    public function notifyCallback()
    {
        Pay::config($this->config);
        
        try{
            $data = Pay::alipay()->callback(); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
        } catch (\Throwable $e) {
            dd($e);
        }

        return Pay::alipay()->success();
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
                // 必填-商户号
                'mch_id' => '',
                // 选填-v2商户私钥
                'mch_secret_key_v2' => '',
                // 必填-v3商户秘钥
                'mch_secret_key' => '',
                // 必填-商户私钥 字符串或路径
                'mch_secret_cert' => '',
                // 必填-商户公钥证书路径
                'mch_public_cert_path' => '',
                // 必填
                'notify_url' => 'https://yansongda.cn/wechat/notify',
                // 选填-公众号 的 app_id
                'mp_app_id' => '',
                // 选填-小程序 的 app_id
                'mini_app_id' => '',
                // 选填-app 的 app_id
                'app_id' => '',
                // 选填-服务商模式下，子公众号 的 app_id
                'sub_mp_app_id' => '',
                // 选填-服务商模式下，子 app 的 app_id
                'sub_app_id' => '',
                // 选填-服务商模式下，子小程序 的 app_id
                'sub_mini_app_id' => '',
                // 选填-服务商模式下，子商户id
                'sub_mch_id' => '',
                // 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
                'wechat_public_cert_path' => [
                    '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatpay_45F***D57.pem',
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
        Pay::config($this->config);
        
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

        $pay = Pay::wechat()->mp($order);

        // $pay->appId
        // $pay->timeStamp
        // $pay->nonceStr
        // $pay->package
        // $pay->signType
    }

    public function callback()
    {
        Pay::config($this->config);
        
        try{
            $data = Pay::wechat()->callback(); // 是的，验签就这么简单！
        } catch (\Throwable $e) {
            dd($e);
        }
        
        return Pay::wechat()->success();
    }
}
```

### 抖音
```php
<?php

namespace App\Http\Controllers;

use Yansongda\Pay\Pay;

class DouyinController
{
    protected $config = [
        'douyin' => [
            'default' => [
                // 选填-商户号
                // 抖音开放平台 --> 应用详情 --> 支付信息 --> 产品管理 --> 商户号
                'mch_id' => '73744242495132490630',
                // 必填-支付 Token，用于支付回调签名
                // 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> Token(令牌)
                'mch_secret_token' => 'douyin_mini_token',
                // 必填-支付 SALT，用于支付签名
                // 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> SALT
                'mch_secret_salt' => 'oDxWDBr4U7FAAQ8hnGDm29i4A6pbTMDKme4WLLvA',
                // 必填-小程序 app_id
                // 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> 小程序appid
                'mini_app_id' => 'tt226e54d3bd581bf801',
                // 选填-抖音开放平台服务商id
                'thirdparty_id' => '',
                // 选填-抖音支付回调地址
                'notify_url' => 'https://yansongda.cn/douyin/notify',
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

    public function pay()
    {
        Pay::config($this->config);
        
        $result = Pay::douyin()->mini([
            'out_order_no' => date('YmdHis').mt_rand(1000, 9999),
            'total_amount' => 1,
            'subject' => '闫嵩达 - test - subject - 01',
            'body' => '闫嵩达 - test - body - 01',
            'valid_time' => 600,
            'expand_order_info' => json_encode([
                'original_delivery_fee' => 15,
                'actual_delivery_fee' => 10
            ])
        ]);
        
        return $result;
    }

    public function callback()
    {
        Pay::config($this->config);
    
        try{
            $data = Pay::douyin()->callback(); // 是的，验签就这么简单！
        } catch (\Throwable $e) {
            dd($e)
        }

        return Pay::douyin()->success();
    }
}
```

### 江苏银行(e融支付)
```php
<?php

namespace App\Http\Controllers;

use Yansongda\Pay\Pay;

class JsbController
{
    protected $config = [
        'jsb' => [
            'default' => [
                // 服务代码
                'svr_code' => '',
                // 必填-合作商ID
                'partner_id' => '',
                // 必填-公私钥对编号
                'public_key_code' => '00',
                // 必填-商户私钥(加密签名)
                'mch_secret_cert_path' => '',
                // 必填-商户公钥证书路径(提供江苏银行进行验证签名用)
                'mch_public_cert_path' => '',
                // 必填-江苏银行的公钥(用于解密江苏银行返回的数据)
                'jsb_public_cert_path' => '',
                //支付通知地址
                'notify_url'            => '', 
                // 选填-默认为正常模式。可选为： MODE_NORMAL:正式环境, MODE_SANDBOX:测试环境
                'mode' => Pay::MODE_NORMAL,
            ]
        ],
        'logger' => [ // optional
            'enable' => false,
            'file' => './logs/epay.log',
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
        Pay::config($this->config);
        
        $order = [
            'outTradeNo' => time().'',
            'proInfo' => 'subject-测试',
            'totalFee'=> 1,
        ];

        $pay = Pay::jsb()->scan($order);
    }

    public function notifyCallback()
    {
        Pay::config($this->config);

        try{
            $data = Pay::jsb()->callback(); // 是的，验签就这么简单！
        } catch (\Throwable $e) {
            dd($e);
        }
        
        return Pay::jsb()->success();
    }
}
```

## 代码贡献

由于测试及使用环境的限制，本项目中只开发了「支付宝」、「微信支付」、「抖音支付」、「银联」、「江苏银行」的相关支付网关。

如果您有其它支付网关的需求，或者发现本项目中需要改进的代码，**_欢迎 Fork 并提交 PR！_**

## 赏一杯咖啡吧

![pay](https://cdn.jsdelivr.net/gh/yansongda/pay/web/public/images/pay.jpg)

## LICENSE

MIT

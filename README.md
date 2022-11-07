<p align="center">
      <a href="https://pay.yansongda.cn" target="_blank" rel="noopener noreferrer"><img width="200" src="https://pay.yansongda.cn/images/logo.png" alt="Logo"></a>
</p>

<p align="center">
    <a href="https://codecov.io/gh/yansongda/pay" ><img src="https://codecov.io/gh/yansongda/pay/branch/master/graph/badge.svg?token=tYMV0YT5jj"/></a>
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
- 符合 PSR2、PSR3、PSR4、PSR7、PSR11、PSR14、PSR18 等各项标准，你可以各种方便的与你的框架集成

## 运行环境
- PHP 7.3+ (v3.1.0 开始需 7.4+)
- composer

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
- ...
- ~~刷卡支付，微信v3版暂不支持，计划后续内置支持v2版，或直接使用 Pay v2 版本~~
- ~~普通红包，微信v3版暂不支持，计划后续内置支持v2版，或直接使用 Pay v2 版本~~
- ~~分裂红包，微信v3版暂不支持，计划后续内置支持v2版，或直接使用 Pay v2 版本~~

### 银联

- 手机网站支付
- 电脑网站支付
- 刷卡支付
- 扫码支付
- ...

## 安装
```shell
composer require yansongda/pay:~3.2.0 -vvv
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
                // 必填-应用私钥 字符串或路径，在 https://open.alipay.com/develop/manage 《应用详情->开发设置->接口加签方式》中设置
                'app_secret_cert' => '89iZ2iC16H6/6a3YcP+hDZUjiNGQx9cuwi9eJyykvcwhD...',
                // 必填-应用公钥证书 路径，设置应用私钥后，即可下载得到以下3个证书
                'app_public_cert_path' => '/Users/yansongda/pay/cert/appCertPublicKey_2016082000295641.crt',
                // 必填-支付宝公钥证书 路径
                'alipay_public_cert_path' => '/Users/yansongda/pay/cert/alipayCertPublicKey_RSA2.crt',
                // 必填-支付宝根证书 路径
                'alipay_root_cert_path' => '/Users/yansongda/pay/cert/alipayRootCert.crt',
                'return_url' => 'https://yansongda.cn/alipay/return',
                'notify_url' => 'https://yansongda.cn/alipay/notify',
                // 选填-第三方应用授权token
                'app_auth_token'          => '',
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
        //如需直接返回生成的 form 跳转表单，可使用以下方法
        //return $result->getBody()->getContents();
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

        return $alipay->success(); //向支付宝服务器返回确认,内容为 success ，如未确认，支付宝会间隔多次发送回调
    }

    //查询普通支付订单 trade_status = TRADE_SUCCESS 交易支付成功; trade_status = TRADE_FINISHED 交易结束，不可退款(交易结束12个月后不可退款)。
    public function find($orderno)
    {
        $alipay = Pay::alipay($this->config);

        $order = array(
            'out_trade_no' => $orderno,
        );
        return $alipay->find($order);
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
                // 必填-商户号，服务商模式下为服务商商户号，可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
                'mch_id' => '',
                // 必填-商户秘钥，即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
                'mch_secret_key' => '',
                // 必填-商户私钥 字符串或路径，即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
                'mch_secret_cert' => '', //文件名形如：apiclient_key.pem
                // 必填-商户公钥证书路径，即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
                'mch_public_cert_path' => '', //文件名形如：apiclient_cert.pem
                // 必填
                'notify_url' => 'https://yansongda.cn/wechat/notify', //微信回调url不能有参数，如?号，空格等，否则会无法正确回调
                // 选填-公众号 的 app_id，可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
                'mp_app_id' => '', //形如 wx56**********0a21
                // 选填-小程序 的 app_id
                'mini_app_id' => '', //形如 wx56**********0a21
                // 选填-app 的 app_id
                'app_id' => '', //形如 wx56**********0a21
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
                // 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
                // 推荐使用官方工具 CertificateDownloader.jar，在传入 商户秘钥、商户号、商户私钥证书、证书序列号 后获得，工具地址：https://github.com/wechatpay-apiv3/CertificateDownloader/
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
            //'verify'        => false, //如未配置curl证书，可开启此项（不推荐），证书下载：https://curl.haxx.se/ca/cacert.pem, 写在 php.ini 中 curl.cainfo = 后面，需使用cacert.pem的绝对路径
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
    ];

    public function index()
    {
        $order = [
            'out_trade_no' => time().'', //微信订单号必须转为string类型，int类型将导致报错
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
        
        return $pay->success(); //向微信服务器返回确认,http 200 状态即可，内容可以为空，这里为 success ，如未确认，微信会间隔多次发送回调
    }

    //查询普通支付订单 交易成功判断条件： trade_state 为 SUCCESS
    public function find($out_trade_no)
    {
        $pay = Pay::wechat($this->config);

        $order = array(
            'out_trade_no' => strval($out_trade_no),
        );

        return $pay->find($order);
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

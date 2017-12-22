<h1 align="center">Pay</h1>

<p align="center">
<a href="https://styleci.io/repos/100355112"><img src="https://styleci.io/repos/100355112/shield?branch=master" alt="StyleCI"></a>
<a href="https://scrutinizer-ci.com/g/yansongda/pay/?branch=master"><img src="https://scrutinizer-ci.com/g/yansongda/pay/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality"></a>
<a href="https://scrutinizer-ci.com/g/yansongda/pay/build-status/master"><img src="https://scrutinizer-ci.com/g/yansongda/pay/badges/build.png?b=master" alt="Build Status"></a>
<a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/v/unstable" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/yansongda/pay"><img src="https://poser.pugx.org/yansongda/pay/license" alt="License"></a>
</p>

开发了多次支付宝与微信支付后，很自然产生一种反感，惰性又来了，想在网上找相关的轮子，可是一直没有找到一款自己觉得逞心如意的，要么使用起来太难理解，要么文件结构太杂乱，只有自己撸起袖子干了。

**说明，请先熟悉支付宝说明文档！！**

欢迎 Star，欢迎 PR！

laravel 扩展包请 [传送至这里](https://github.com/yansongda/laravel-pay)

## 特点
- 命名不那么乱七八糟
- 隐藏开发者不需要关注的细节
- 根据支付宝、微信最新 API 开发而成
- 高度抽象的类，免去各种拼json与xml的痛苦
- 符合 PSR 标准，你可以各种方便的与你的框架集成
- 文件结构清晰易理解，可以随心所欲添加本项目中没有的支付网关
- 方法使用更优雅，不必再去研究那些奇怪的的方法名或者类名是做啥用的


## 运行环境
- PHP 5.6+
- composer


## 支持的支付网关

由于各支付网关参差不齐，所以我们抽象了两个方法 `driver()`，`gateway()`。

两个方法的作用如下：

`driver()` ： 确定支付平台，如 `alipay`,`wechat`;  

`gateway()`： 确定支付网关。通过此方法，确定支付平台下的支付网关。例如，支付宝下有 「电脑网站支付」，「手机网站支付」，「APP 支付」三种支付网关，通过传入 `web`,`wap`,`app` 确定。

详细思路可以查看源代码。

### 1、支付宝

- 电脑支付
- 手机网站支付
- APP 支付
- 刷卡支付
- 扫码支付

SDK 中对应的 driver 和 gateway 如下表所示：  

| driver | gateway |   描述       |
| :----: | :-----: | :-------:   |
| alipay | web     | 电脑支付     |
| alipay | wap     | 手机网站支付  |
| alipay | app     | APP 支付  |
| alipay | pos     | 刷卡支付  |
| alipay | scan    | 扫码支付  |
| alipay | transfer    | 帐户转账（可用于平台用户提现）  |
  
### 2、微信

- 公众号支付
- 小程序支付
- H5 支付
- 扫码支付
- 刷卡支付
- APP 支付

SDK 中对应的 driver 和 gateway 如下表所示：

| driver | gateway |   描述     |
| :----: | :-----: | :-------: |
| wechat | mp      | 公众号支付  |
| wechat | miniapp | 小程序支付  |
| wechat | wap     | H5 支付    |
| wechat | scan    | 扫码支付    |
| wechat | pos     | 刷卡支付    |
| wechat | app     | APP 支付  |
| wechat | transfer     | 企业付款  |

## 支持的方法

所有网关均支持以下方法

- pay(array $config_biz)  
说明：支付接口  
参数：数组类型，订单业务配置项，包含 订单号，订单金额等  
返回：mixed  详情请看「支付网关配置说明与返回值」一节。 

- refund(array|string $config_biz, $refund_amount = null)  
说明：退款接口  
参数：`$config_biz` 为字符串类型仅对`支付宝支付`有效，此时代表订单号，第二个参数为退款金额。  
返回：mixed  退款成功，返回 服务器返回的数组；否则返回 false；  

- close(array|string $config_biz)  
说明：关闭订单接口  
参数：`$config_biz` 为字符串类型时代表订单号，如果为数组，则为关闭订单业务配置项，配置项内容请参考各个支付网关官方文档。  
返回：mixed  关闭订单成功，返回 服务器返回的数组；否则返回 false；  

- find(string $out_trade_no)  
说明：查找订单接口  
参数：`$out_trade_no` 为订单号。  
返回：mixed  查找订单成功，返回 服务器返回的数组；否则返回 false；  

- verify($data, $sign = null)  
说明：验证服务器返回消息是否合法  
参数：`$data` 为服务器接收到的原始内容，`$sign` 为签名信息，当其为空时，系统将自动转化 `$data` 为数组，然后取 `$data['sign']`。  
返回：mixed  验证成功，返回 服务器返回的数组；否则返回 false；  


## 安装
```shell
composer require yansongda/pay
```

## 使用说明

### 0、一个完整的例子:
```php
<?php

namespace App\Http\Controllers;

use Yansongda\Pay\Pay;
use Illuminate\Http\Request;

class PayController extends Controller
{
    protected $config = [
        'alipay' => [
            'app_id' => '2016082000295641',
            'notify_url' => 'http://yansongda.cn/alipay_notify.php',
            'return_url' => 'http://yansongda.cn/return.php',
            'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuWJKrQ6SWvS6niI+4vEVZiYfjkCfLQfoFI2nCp9ZLDS42QtiL4Ccyx8scgc3nhVwmVRte8f57TFvGhvJD0upT4O5O/lRxmTjechXAorirVdAODpOu0mFfQV9y/T9o9hHnU+VmO5spoVb3umqpq6D/Pt8p25Yk852/w01VTIczrXC4QlrbOEe3sr1E9auoC7rgYjjCO6lZUIDjX/oBmNXZxhRDrYx4Yf5X7y8FRBFvygIE2FgxV4Yw+SL3QAa2m5MLcbusJpxOml9YVQfP8iSurx41PvvXUMo49JG3BDVernaCYXQCoUJv9fJwbnfZd7J5YByC+5KM4sblJTq7bXZWQIDAQAB',
            'private_key' => 'MIIEpAIBAAKCAQEAs6+F2leOgOrvj9jTeDhb5q46GewOjqLBlGSs/bVL4Z3fMr3p+Q1Tux/6uogeVi/eHd84xvQdfpZ87A1SfoWnEGH5z15yorccxSOwWUI+q8gz51IWqjgZxhWKe31BxNZ+prnQpyeMBtE25fXp5nQZ/pftgePyUUvUZRcAUisswntobDQKbwx28VCXw5XB2A+lvYEvxmMv/QexYjwKK4M54j435TuC3UctZbnuynSPpOmCu45ZhEYXd4YMsGMdZE5/077ZU1aU7wx/gk07PiHImEOCDkzqsFo0Buc/knGcdOiUDvm2hn2y1XvwjyFOThsqCsQYi4JmwZdRa8kvOf57nwIDAQABAoIBAQCw5QCqln4VTrTvcW+msB1ReX57nJgsNfDLbV2dG8mLYQemBa9833DqDK6iynTLNq69y88ylose33o2TVtEccGp8Dqluv6yUAED14G6LexS43KtrXPgugAtsXE253ZDGUNwUggnN1i0MW2RcMqHdQ9ORDWvJUCeZj/AEafgPN8AyiLrZeL07jJz/uaRfAuNqkImCVIarKUX3HBCjl9TpuoMjcMhz/MsOmQ0agtCatO1eoH1sqv5Odvxb1i59c8Hvq/mGEXyRuoiDo05SE6IyXYXr84/Nf2xvVNHNQA6kTckj8shSi+HGM4mO1Y4Pbb7XcnxNkT0Inn6oJMSiy56P+CpAoGBAO1O+5FE1ZuVGuLb48cY+0lHCD+nhSBd66B5FrxgPYCkFOQWR7pWyfNDBlmO3SSooQ8TQXA25blrkDxzOAEGX57EPiipXr/hy5e+WNoukpy09rsO1TMsvC+v0FXLvZ+TIAkqfnYBgaT56ku7yZ8aFGMwdCPL7WJYAwUIcZX8wZ3dAoGBAMHWplAqhe4bfkGOEEpfs6VvEQxCqYMYVyR65K0rI1LiDZn6Ij8fdVtwMjGKFSZZTspmsqnbbuCE/VTyDzF4NpAxdm3cBtZACv1Lpu2Om+aTzhK2PI6WTDVTKAJBYegXaahBCqVbSxieR62IWtmOMjggTtAKWZ1P5LQcRwdkaB2rAoGAWnAPT318Kp7YcDx8whOzMGnxqtCc24jvk2iSUZgb2Dqv+3zCOTF6JUsV0Guxu5bISoZ8GdfSFKf5gBAo97sGFeuUBMsHYPkcLehM1FmLZk1Q+ljcx3P1A/ds3kWXLolTXCrlpvNMBSN5NwOKAyhdPK/qkvnUrfX8sJ5XK2H4J8ECgYAGIZ0HIiE0Y+g9eJnpUFelXvsCEUW9YNK4065SD/BBGedmPHRC3OLgbo8X5A9BNEf6vP7fwpIiRfKhcjqqzOuk6fueA/yvYD04v+Da2MzzoS8+hkcqF3T3pta4I4tORRdRfCUzD80zTSZlRc/h286Y2eTETd+By1onnFFe2X01mwKBgQDaxo4PBcLL2OyVT5DoXiIdTCJ8KNZL9+kV1aiBuOWxnRgkDjPngslzNa1bK+klGgJNYDbQqohKNn1HeFX3mYNfCUpuSnD2Yag53Dd/1DLO+NxzwvTu4D6DCUnMMMBVaF42ig31Bs0jI3JQZVqeeFzSET8fkoFopJf3G6UXlrIEAQ==',
        ],
    ];

    public function index()
    {
        $config_biz = [
            'out_trade_no' => time(),
            'total_amount' => '1',
            'subject'      => 'test subject',
        ];

        $pay = new Pay($this->config);

        return $pay->driver('alipay')->gateway()->pay($config_biz);
    }

    public function return(Request $request)
    {
        $pay = new Pay($this->config);

        return $pay->driver('alipay')->gateway()->verify($request->all());
    }

    public function notify(Request $request)
    {
        $pay = new Pay($this->config);

        if ($pay->driver('alipay')->gateway()->verify($request->all())) {
            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。 
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号； 
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）； 
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）； 
            // 4、验证app_id是否为该商户本身。 
            // 5、其它业务逻辑情况
            file_put_contents(storage_path('notify.txt'), "收到来自支付宝的异步通知\r\n", FILE_APPEND);
            file_put_contents(storage_path('notify.txt'), '订单号：' . $request->out_trade_no . "\r\n", FILE_APPEND);
            file_put_contents(storage_path('notify.txt'), '订单金额：' . $request->total_amount . "\r\n\r\n", FILE_APPEND);
        } else {
            file_put_contents(storage_path('notify.txt'), "收到异步通知\r\n", FILE_APPEND);
        }

        echo "success";
    }
}

```
```php
<?php

namespace App\Http\Controllers;

use Yansongda\Pay\Pay;
use Illuminate\Http\Request;

class PayController extends Controller
{
    protected $config = [
        'wechat' => [
            'app_id' => 'wxb3f6xxxxxxxxxx',
            'mch_id' => '1457xxxxx2',
            'notify_url' => 'http://yansongda.cn/wechat_notify.php',
            'key' => 'mF2suE9sU6Mk1Cxxxxxxxxxx45',
            'cert_client' => './apiclient_cert.pem',
            'cert_key' => './apiclient_key.pem',
        ],
    ];

    public function index()
    {
        $config_biz = [
            'out_trade_no' => 'e2',
            'total_fee' => '1', // **单位：分**
            'body' => 'test body',
            'spbill_create_ip' => '8.8.8.8',
            'openid' => 'onkVf1FjWS5SBIihS-123456_abc',
        ];

        $pay = new Pay($this->config);

        return $pay->driver('wechat')->gateway('mp')->pay($config_biz);
    }

    public function notify(Request $request)
    {
        $pay = new Pay($this->config);
        $verify = $pay->driver('wechat')->gateway('mp')->verify($request->getContent());

        if ($verify) {
            file_put_contents('notify.txt', "收到来自微信的异步通知\r\n", FILE_APPEND);
            file_put_contents('notify.txt', '订单号：' . $verify['out_trade_no'] . "\r\n", FILE_APPEND);
            file_put_contents('notify.txt', '订单金额：' . $verify['total_fee'] . "\r\n\r\n", FILE_APPEND);
        } else {
            file_put_contents(storage_path('notify.txt'), "收到异步通知\r\n", FILE_APPEND);
        }

        echo "success";
    }
}

```

### 1、准备配置参数

```php
<?php

$config = [
    'alipay' => [
        'app_id' => '',             // 支付宝提供的 APP_ID
        'ali_public_key' => '',     // 支付宝公钥，1行填写
        'private_key' => '',        // 自己的私钥，1行填写
    ],
];
$config_biz = [
    'out_trade_no' => '12',         // 订单号
    'total_amount' => '13',         // 订单金额，单位：元，**微信支付，单位：分**
    'subject' => 'test subject',    // 订单商品标题
];
```

### 2、在代码中使用

```php
<?php

$pay = new Pay($config);
return $pay->driver('alipay')->gateway('web')->pay($config_biz);
```


## 错误

使用非跳转接口（如， `refund` 接口,`close` 接口）时，如果在调用相关支付网关 API 时有错误产生，会抛出 `GatewayException` 错误，可以通过 `$e->getMessage()` 查看，同时，也可通过 `$e->raw` 查看调用 API 后返回的原始数据，该值为数组格式。


## 支付网关配置说明与返回值

由于支付网关不同，每家参数参差不齐，为了方便，我们抽象定义了两个参数：`$config`,`$config_biz`，分别为全局参数，业务参数。但是，所有配置参数均为官方标准参数，无任何差别。

「业务参数」为订单相关的参数，「全局参数」为除订单相关参数以外的全局性参数。

具体参数列表请查看每个支付网关的使用说明。

### 1、支付宝 - 电脑网站支付

#### 最小配置参数
```php
<?php

$config = [
    'alipay' => [
        'app_id' => '',             // 支付宝提供的 APP_ID
        'ali_public_key' => '',     // 支付宝公钥，1行填写
        'private_key' => '',        // 自己的私钥，1行填写
    ],
];
$config_biz = [
    'out_trade_no' => '12',                 // 订单号
    'total_amount' => '13',                 // 订单金额，单位：元
    'subject' => 'test subject',   // 订单商品标题
];
```

#### 所有配置参数

所有参数均为官方标准参数，无任何差别。[点击这里](https://docs.open.alipay.com/common/105901 '支付宝官方文档') 查看官方文档。

```php
<?php

$config = [
    'alipay' => [
        'app_id' => '',             // 支付宝提供的 APP_ID
        'ali_public_key' => '',     // 支付宝公钥，1行填写
        'private_key' => '',        // 自己的私钥，1行填写
        'return_url' => '',         // 同步通知 url，*强烈建议加上本参数*
        'notify_url' => '',         // 异步通知 url，*强烈建议加上本参数*
    ],
];
$config_biz = [
    'out_trade_no' => '',
    'total_amount' => '',                 
    'subject' => '',

    // 订单描述
    'body' => '',

    // 订单包含的商品列表信息，Json格式： {"show_url":"https://或http://打头的商品的展示地址"} ，在支付时，可点击商品名称跳转到该地址      
    'goods_detail' => '',

    // 该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。该参数在请求到支付宝时开始计时。
    'timeout_express' => '',                
    
    // 禁用渠道，用户不可用指定渠道支付当有多个渠道时用“,”分隔注：与enable_pay_channels互斥
    'disable_pay_channels' => '',

    // 可用渠道，用户只能在指定渠道范围内支付当有多个渠道时用“,”分隔注：与disable_pay_channels互斥
    'enable_pay_channels' => '',

    // 公用回传参数，如果请求时传递了该参数，则返回给商户时会回传该参数。支付宝只会在异步通知时将该参数原样返回。本参数必须进行UrlEncode之后才可以发送给支付宝
    'passback_params' => '',

    // 业务扩展参数，详见 [业务扩展参数说明](https://docs.open.alipay.com/#kzcs)
    'extend_params' => '',

    // 商品主类型：0—虚拟类商品，1—实物类商品（默认）注：虚拟类商品不支持使用花呗渠道
    'goods_type' => '',

    // 获取用户授权信息，可实现如免登功能。获取方法请查阅：用户信息授权
    'auth_token' => '',

    /**
     *  PC扫码支付的方式，支持前置模式和跳转模式。
     *
     *  前置模式是将二维码前置到商户的订单确认页的模式。需要商户在自己的页面中以iframe方式请求支付宝页面。具体分为以下几种：
     *  0：订单码-简约前置模式，对应iframe宽度不能小于600px，高度不能小于300px；
     *  1：订单码-前置模式，对应iframe宽度不能小于300px，高度不能小于600px；
     *  3：订单码-迷你前置模式，对应iframe宽度不能小于75px，高度不能小于75px；
     *  4：订单码-可定义宽度的嵌入式二维码，商户可根据需要设定二维码的大小。
     *
     *  跳转模式下，用户的扫码界面是由支付宝生成的，不在商户的域名下。
     *  2：订单码-跳转模式
     */
    'qr_pay_mode' => '',

    // 商户自定义二维码宽度 注：qr_pay_mode=4时该参数生效
    'qrcode_width' => ''
];
```

#### 返回值
- pay()  
类型：string  
说明：该接口返回跳转到支付宝支付的 Html 代码。  

### 2、支付宝 - 手机网站支付

#### 最小配置参数
```php
<?php

$config = [
    'alipay' => [
        'app_id' => '',             // 支付宝提供的 APP_ID
        'ali_public_key' => '',     // 支付宝公钥，1行填写
        'private_key' => '',        // 自己的私钥，1行填写
    ],
];
$config_biz = [
    'out_trade_no' => '12',                 // 订单号
    'total_amount' => '13',                 // 订单金额，单位：元
    'subject' => 'test subject',   // 订单商品标题
];
```

#### 所有配置参数

该网关大部分参数和 「电脑支付」 相同，具体请参考 [官方文档](https://docs.open.alipay.com/203/107090/ '支付宝手机网站支付文档')

#### 返回值
- pay()  
类型：string  
说明：该接口返回跳转到支付宝支付的 Html 代码。  

### 3、支付宝 - APP 支付

#### 最小配置参数
```php
<?php

$config = [
    'alipay' => [
        'app_id' => '',             // 支付宝提供的 APP_ID
        'notify_url' => '',         // 支付宝异步通知地址
        'ali_public_key' => '',     // 支付宝公钥，1行填写
        'private_key' => '',        // 自己的私钥，1行填写
    ],
];
$config_biz = [
    'out_trade_no' => '12',                 // 订单号
    'total_amount' => '13',                 // 订单金额，单位：元
    'subject' => 'test subject',   // 订单商品标题
];
```

#### 所有配置参数
该网关大部分参数和 「电脑支付」 相同，具体请参考 [官方文档](https://docs.open.alipay.com/204/105465/ '支付宝APP支付文档')

#### 返回值
- pay()  
类型：string  
说明：该接口返回用于客户端调用的 orderString 字符串，可直接供 APP 客户端调用，客户端调用方法不在此文档讨论范围内，[Android 用户请看这里](https://docs.open.alipay.com/204/105300/)，[Ios 用户请看这里](https://docs.open.alipay.com/204/105299/)。

### 4、支付宝 - 刷卡支付

#### 最小配置参数
```php
<?php

$config = [
    'alipay' => [
        'app_id' => '',             // 支付宝提供的 APP_ID
        'ali_public_key' => '',     // 支付宝公钥，1行填写
        'private_key' => '',        // 自己的私钥，1行填写
    ],
];
$config_biz = [
    'out_trade_no' => '12',         // 订单号
    'total_amount' => '13',         // 订单金额，单位：元
    'subject' => 'test subject',    // 订单商品标题
    'auth_code'  => '123456',       // 授权码
];
```

#### 所有配置参数
该网关大部分参数和 「电脑支付」 相同，具体请参考 [官方文档](https://docs.open.alipay.com/api_1/alipay.trade.pay ' 支付宝APP支付文档')

#### 返回值
- pay()  
类型：array|bool  
说明：该接口成功时返回服务器响应的数组；验签失败返回 false。  

### 5、支付宝 - 扫码支付

#### 最小配置参数
```php
<?php

$config = [
    'alipay' => [
        'app_id' => '',             // 支付宝提供的 APP_ID
        'notify_url' => '',         // 支付宝异步通知地址
        'ali_public_key' => '',     // 支付宝公钥，1行填写
        'private_key' => '',        // 自己的私钥，1行填写
    ],
];
$config_biz = [
    'out_trade_no' => '12',                 // 订单号
    'total_amount' => '13',                 // 订单金额，单位：元
    'subject' => 'test subject',   // 订单商品标题
];
```

#### 所有配置参数
该网关大部分参数和 「电脑支付」 相同，具体请参考 [官方文档](https://docs.open.alipay.com/api_1/alipay.trade.precreate ' 支付宝APP支付文档')

#### 返回值
- pay()  
类型：string  
说明：该接口返回二维码链接，可以通过其他库转换为二维码供用户扫描。

### 6、支付宝 - 帐户转账

#### 最小配置参数
```php
<?php

$config = [
    'alipay' => [
        'app_id' => '',             // 支付宝提供的 APP_ID
        'ali_public_key' => '',     // 支付宝公钥，1行填写
        'private_key' => '',        // 自己的私钥，1行填写
    ],
];
$config_biz = [
    'out_biz_no' => '',                      // 订单号
    'payee_type' => 'ALIPAY_LOGONID',        // 收款方账户类型(ALIPAY_LOGONID | ALIPAY_USERID)
    'payee_account' => 'demo@sandbox.com',   // 收款方账户
    'amount' => '10',                        // 转账金额
];
```

#### 所有配置参数
```php
<?php

$config = [
    'alipay' => [
        'app_id' => '',             // 支付宝提供的 APP_ID
        'ali_public_key' => '',     // 支付宝公钥，1行填写
        'private_key' => '',        // 自己的私钥，1行填写
    ],
];
$config_biz = [
    'out_biz_no' => '',                      // 订单号
    'payee_type' => 'ALIPAY_LOGONID',        // 收款方账户类型(ALIPAY_LOGONID | ALIPAY_USERID)
    'payee_account' => 'demo@sandbox.com',   // 收款方账户
    'amount' => '10',                        // 转账金额
    'payer_show_name' => '未寒',             // 付款方姓名
    'payee_real_name' => '张三',             // 收款方真实姓名
    'remark' => '张三',                      // 转账备注
];
```

 [官方文档](https://doc.open.alipay.com/docs/api.htm?apiId=1321&docType=4 ' 单笔转账到支付宝账户接口')


#### 返回值
- pay()  
类型：array|bool  
说明：该接口成功时返回服务器响应的数组；验签失败返回 false。

### 7、微信 - 公众号支付

#### 最小配置参数
```php
<?php

$config = [
    'wechat' => [
        'app_id' => '',             // 公众号APPID
        'mch_id' => '',             // 微信商户号
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
    'openid' => '',                 // 支付人的 openID
];
```

#### 所有配置参数
所有参数均为官方标准参数，无任何差别。[点击这里](https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1 '微信支付官方文档') 查看官方文档。
```php
<?php

$config = [
    'wechat' => [
        'endpoint_url' => 'https://apihk.mch.weixin.qq.com/', // optional, default 'https://api.mch.weixin.qq.com/'
        'app_id' => '',             // 公众号APPID
        'mch_id' => '',             // 微信商户号
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
    'openid' => '',                 // 支付人的 openID
    
    // 自定义参数，可以为终端设备号(门店号或收银设备ID)，PC网页或公众号内支付可以传"WEB"
    'device_info' => '',
    
    // 商品详细描述，对于使用单品优惠的商户，改字段必须按照规范上传，详见“单品优惠参数说明”
    'detail' => '',
    
    // 附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用。
    'attach' => '',
    
    // 符合ISO 4217标准的三位字母代码，默认人民币：CNY，详细列表请参见货币类型
    'fee_type' => '',
    
    // 订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
    'time_start' => '',
    
    // 订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。其他详见时间规则注意：最短失效时间间隔必须大于5分钟
    'time_expire' => '',
    
    // 订单优惠标记，使用代金券或立减优惠功能时需要的参数，说明详见代金券或立减优惠
    'goods_tag' => '',
    
    // trade_type=NATIVE时（即扫码支付），此参数必传。此参数为二维码中包含的商品ID，商户自行定义。
    'product_id' => '',
    
    // 上传此参数no_credit--可限制用户不能使用信用卡支付
    'limit_pay' => '',
    
    // 该字段用于上报场景信息，目前支持上报实际门店信息。该字段为JSON对象数据，对象格式为{"store_info":{"id": "门店ID","name": "名称","area_code": "编码","address": "地址" }} ，字段详细说明请点击行前的+展开
    'scene_info' => '',
];
```

#### 返回值
- pay()  
类型：array  
说明：返回用于 微信内H5调起支付 的所需参数数组。后续调用不在本文档讨论范围内，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_7&index=6)。

后续调用举例：  

```html
<script type="text/javascript">
        function onBridgeReady(){
            WeixinJSBridge.invoke(
                'getBrandWCPayRequest', {
                    "appId":"<?php echo $pay['appId']; ?>",     //公众号名称，由商户传入     
                    "timeStamp":"<?php echo $pay['timeStamp']; ?>",         //时间戳，自1970年以来的秒数     
                    "nonceStr":"<?php echo $pay['nonceStr']; ?>", //随机串     
                    "package":"<?php echo $pay['package']; ?>",     
                    "signType":"<?php echo $pay['signType']; ?>",         //微信签名方式：     
                    "paySign":"<?php echo $pay['paySign']; ?>" //微信签名 
                },
                function(res){     
                    if(res.err_msg == "get_brand_wcpay_request:ok" ) {}     // 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回    ok，但并不保证它绝对可靠。 
                }
            ); 
        }

        $(function(){
            $('#pay').click(function(){
                if (typeof WeixinJSBridge == "undefined"){
                   if( document.addEventListener ){
                       document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                   }else if (document.attachEvent){
                       document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
                       document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                   }
                }else{
                   onBridgeReady();
                }
            })
        });
</script>
```

### 8、微信 - 小程序支付

#### 最小配置参数
```php
<?php

$config = [
    'wechat' => [
        'miniapp_id' => '',             // 小程序APPID
        'mch_id' => '',             // 微信商户号
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
    'openid' => '',                 // 支付人的 openID
];
```

#### 所有配置参数
由于「小程序支付」和「公众号支付」都使用的是 JSAPI，所以，除了 APPID 一个使用的是公众号的 APPID 一个使用的是 小程序的 APPID 以外，该网关所有参数和 「公众号支付」 相同，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1)。

#### 返回值
- pay()  
类型：array  
说明：返回用于 小程序调起支付API 的所需参数数组。后续调用不在本文档讨论范围内，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=7_7&index=3)。

### 9、微信 - H5 支付
#### 最小配置参数

```php
<?php

$config = [
    'wechat' => [
        'app_id' => '',             // 微信公众号 APPID
        'mch_id' => '',             // 微信商户号
        'return_url' => '',         // *此配置选项可选*，注意，该跳转 URL 只有跳转之意，没有同步通知功能
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
];
```

#### 所有配置参数
所有配置项和前面支付网关相差不大，请[点击这里查看](https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_20&index=1).

#### 返回值
- pay()  
类型：string  
说明：返回微信支付中间页网址，可直接 302 跳转。

### 10、微信 - 扫码支付
这里使用「模式二」进行扫码支付，具体请[参考这里](https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=6_5)

#### 最小配置参数

```php
<?php

$config = [
    'wechat' => [
        'app_id' => '',             // 微信公众号 APPID
        'mch_id' => '',             // 微信商户号
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 调用 API 服务器的 IP
    'product_id' => '',             // 订单商品 ID
];
```

#### 所有配置参数
所有配置项和前面支付网关相差不大，请[点击这里查看](https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=9_1)

#### 返回值
- pay()  
类型：string  
说明：返回微信支付二维码 URL 地址，可直接将此 url 生成二维码，展示给用户进行扫码支付。

### 11、微信 - 刷卡支付

#### 最小配置参数
```php
<?php

$config = [
    'wechat' => [
        'app_id' => '',             // 公众号 APPID
        'mch_id' => '',             // 微信商户号
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
    'auth_code' => '',              // 授权码
];
```

#### 所有配置参数
该网关所有参数和其它支付网关基本相同，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1)。

#### 返回值
- pay()  
类型：array  
说明：返回用于服务器返回的数组。返回参数请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1)。

### 12、微信 - APP 支付

#### 最小配置参数
```php
<?php

$config = [
    'wechat' => [
        'appid' => '',              // APPID
        'mch_id' => '',             // 微信商户号
        'notify_url' => '',
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'out_trade_no' => '',           // 订单号
    'total_fee' => '',              // 订单金额，**单位：分**
    'body' => '',                   // 订单描述
    'spbill_create_ip' => '',       // 支付人的 IP
];
```

#### 所有配置参数
该网关所有参数和其它支付网关相同相同，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1)。

#### 返回值
- pay()  
类型：array  
说明：返回用于 小程序调起支付API 的所需参数数组。后续调用不在本文档讨论范围内，具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=8_5)。

### 12、微信 - 企业付款

#### 最小配置参数
```php
<?php

$config = [
    'wechat' => [
        'appid' => '',              // APPID
        'mch_id' => '',             // 微信商户号
        'key' => '',                // 微信支付签名秘钥
        'cert_client' => './apiclient_cert.pem',        // 客户端证书路径，退款时需要用到
        'cert_key' => './apiclient_key.pem',            // 客户端秘钥路径，退款时需要用到
    ],
];

$config_biz = [
    'partner_trade_no' => '',              //商户订单号
    'openid' => '',                        //收款人的openid
    'check_name' => 'NO_CHECK',            //NO_CHECK：不校验真实姓名\FORCE_CHECK：强校验真实姓名
//    're_user_name'=>'张三',              //check_name为 FORCE_CHECK 校验实名的时候必须提交
    'amount' => 100,                       //企业付款金额，单位为分
    'desc' => '帐户提现',                  //付款说明
    'spbill_create_ip' => '192.168.0.1',  //发起交易的IP地址
];
```

#### 所有配置参数
具体请看 [官方文档](https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2)。

#### 返回值
- pay()  
类型：array  
说明：返回用于 支付结果 的数组。具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2)。

### 13、微信 - 发放裂变红包

#### 最小配置参数
```php
<?php
 $config = [
            'wechat' => [
                'app_id'=>'wxaxxxxxxxx',
                'mch_id' => '1442222202',
                'key' => 'ddddddddddddddd',
                'cert_client' => 'D:\php\xxx\application\wxpay\cert\apiclient_cert.pem',
                'cert_key' => 'D:\php\xxx\application\wxpay\cert\apiclient_key.pem',
            ],
        ];

        $config_biz = [
            'wxappid'=>'wxaxxxxxxxx',
            'mch_billno' => 'hb'.time(),
            'send_name'=>'萌点云科技',//商户名称
            're_openid'=>'ogg5JwsssssssssssCdXeD_S54',//用户openid
            'total_amount' =>333, // 付款金额，单位分
            'wishing'=>'提前祝你狗年大吉',//红包祝福语
            'client_ip'=>'192.168.0.1',//调用接口的机器Ip地址
            'total_num'=>'3',//红包发放总人数
            'act_name'=>'提前拜年',//活动名称
            'remark'=>'提前祝你狗年大吉，苟富贵勿相忘！', //备注
            'amt_type'=>'ALL_RAND',//ALL_RAND—全部随机,商户指定总金额和红包发放总人数，由微信支付随机计算出各红包金额
        ];

        $pay = new Pay($config);
        try
        {
            $res=   $pay->driver('wechat')->gateway('groupredpack')->pay($config_biz);

        }catch (Exception $e){

        }

```

#### 所有配置参数
具体请看 [官方文档](https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_5&index=4)。

#### 返回值
- pay()  
类型：array  
说明：返回用于 支付结果 的数组。具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_5&index=4)。


### 14、微信 - 发放普通红包

#### 最小配置参数
```php
<?php
 $config = [
            'wechat' => [
                'app_id'=>'wxaxxxxxxxx',
                'mch_id' => '1442222202',
                'key' => 'ddddddddddddddd',
                'cert_client' => 'D:\php\xxx\application\wxpay\cert\apiclient_cert.pem',
                'cert_key' => 'D:\php\xxx\application\wxpay\cert\apiclient_key.pem',
            ],
        ];

        $config_biz = [
            'wxappid'=>'wxaxxxxxxxx',
            'mch_billno' => 'hb'.time(),
            'send_name'=>'萌点云科技',//商户名称
            're_openid'=>'ogg5JwsssssssssssCdXeD_S54',//用户openid
            'total_amount' =>100, // 付款金额，单位分
            'wishing'=>'提前祝你狗年大吉',//红包祝福语
            'client_ip'=>'192.168.0.1',//调用接口的机器Ip地址
            'total_num'=>'1',//红包发放总人数
            'act_name'=>'提前拜年',//活动名称
            'remark'=>'提前祝你狗年大吉，苟富贵勿相忘！', //备注
        ];

        $pay = new Pay($config);
        try
        {
            $res=   $pay->driver('wechat')->gateway('redpack')->pay($config_biz);

        }catch (Exception $e){

        }

```

#### 所有配置参数
具体请看 [官方文档](https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_4&index=3)。

#### 返回值
- pay()  
类型：array  
说明：返回用于 支付结果 的数组。具体请 [参考这里](https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_4&index=3)。
## 代码贡献
由于测试及使用环境的限制，本项目中只开发了「支付宝」和「微信支付」的相关支付网关。

如果您有其它支付网关的需求，或者发现本项目中需要改进的代码，**_欢迎 Fork 并提交 PR！_**

## LICENSE
MIT

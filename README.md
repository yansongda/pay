<h1 align="center">Pay</h1>

项目概述blablabla

## 特点


## 运行环境
- PHP 5.6+

## 支持的支付网关

### 1、支付宝

- 电脑支付
- 手机网站支付

SDK 中对应的 driver 和 gateway 如下表所示：  

| driver | gateway |   描述       |
| :----: | :-----: | :-------:   |
| alipay | web     | 电脑支付     |
| alipay | wap     | 手机网站支付  |
  
### 2、微信

- 公众号支付
- 小程序支付
- H5 支付
- 扫码支付
- 刷卡支付

SDK 中对应的 driver 和 gateway 如下表所示：

| driver | gateway |   描述     |
| :----: | :-----: | :-------: |
| wechat | mp      | 公众号支付  |
| wechat | miniapp | 小程序支付  |
| wechat | wap     | H5 支付    |
| wechat | scan    | 扫码支付    |
| wechat | pos     | 刷卡支付    |

## 支持的方法

所有网关均支持以下方法

- pay    (支付接口)
- refund (退款接口)
- close  (关闭订单接口)

## 配置参数

由于支付网关不同，每家参数参差不齐，为了方便，我们抽象定义了两个参数：`$config`,`$config_biz`，分别为全局参数，业务参数。

但是，所有配置参数均为官方标准参数，无任何差别，为了大家方便，现总结如下：

### 支付宝 - 电脑支付

所有参数均为官方标准参数，无任何差别。[点击这里](https://docs.open.alipay.com/common/105901 '支付宝官方文档') 查看官方文档。

1、最小配置参数
```php
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
    'subject' => 'officeal test subject',   // 订单商品标题
];
```

2、所有配置参数

```php
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


### 支付宝 - 手机网站支付

1、最小配置参数
```php
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
    'subject' => 'officeal test subject',   // 订单商品标题
];
```

2、所有配置参数

该网关大部分参数和 「电脑支付」 相同，具体请参考 [官方文档](https://docs.open.alipay.com/203/107090/ '支付宝手机网站支付文档')

### 微信 - 公众号支付
所有参数均为官方标准参数，无任何差别。[点击这里](https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1 '微信支付官方文档') 查看官方文档。

1、最小配置参数

2、所有配置参数

### 微信 - 小程序支付
该网关大部分参数和 「公众号支付」 相同

### 微信 - H5 支付

### 微信 - 扫码支付

### 微信 - 刷卡支付

## 安装
```composer required yansongda/pay```

## 使用方法
### 1、支付宝
```php
use Yansongda\Pay\Pay;

$config = [
    'alipay' => [
        'app_id' => '',
        'notify_url' => '',
        'return_url' => '',
        'ali_public_key' => '',
        'private_key' => '',
    ],
];
$config_biz = [
    'out_trade_no' => '',
    'total_amount' => '',
    'subject' => '',
];

$pay = new Pay($config);
return $pay->dirver('alipay')->gateway('web')->pay($config_biz);
```

### 2、微信
```php
use Yansongda\Pay\Pay;

$config = [
    'wechat' => [
        'app_id' => '',
        'mch_id' => '',
        'appid' => '',
        'notify_url' => '',
        'return_url' => '',
        'key' => '',
    ],
];
$config_biz = [
    'out_trade_no' => '',
    'total_fee' => '',
    'body' => '',
    'spbill_create_ip' => '',
    'openid' => '',
];

$pay = new Pay($config);
return $pay->dirver('wechat')->gateway('js')->pay($config_biz);
```

## LICENSE
MIT
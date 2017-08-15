<h1 align="center">Pay</h1>

项目概述blablabla

## 特点


## 运行环境
- PHP 5.6+

## 安装
```composer required yansongda/pay```

## 支持的支付网关

### 支付宝

- 电脑支付
- 手机网站支付

SDK 中对应的 driver 和 gateway 如下表所示：

| driver | gateway |   描述       |
| :----: | :-----: | :-------:   |
| alipay | web     | 电脑支付     |
| alipay | wap     | 手机网站支付  |

### 微信

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
<h1 align="center">Pay</h1>

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
        'notify_url' => '',
    ],
];
$config_biz = [
    'out_trade_no' => '',
    'total_fee' => '',
    'body' => '',
    'spbill_create_ip' => '',
];

$pay = new Pay($config);
return $pay->dirver('wechat')->gateway('js')->pay($config_biz);
```

## LICENSE
MIT
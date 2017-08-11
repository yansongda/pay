<h1 align="center">Pay</h1>

## 使用方法
### 1、通过 composer 加载
```composer required yansongda/pay```

### 2、在代码中使用
```php
use Yansongda\Pay\Pay;

$config = [
    'alipay' => [
        'app_id' => '',
        'notify' => '',
        'return' => '',
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
return $pay->dirver('alipay')->gateway('web')->pay($biz);
```

## LICENSE
MIT
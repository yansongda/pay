# 微信快速入门

在初始化完毕后，就可以直接方便的享受 `yansongda/pay`  带来的便利了。

:::tip
`yansongda/pay` v3.x 版本直接支持 微信支付v3 版本，关于微信支付 v2/v3 版本区别，请参考[官方文档](https://pay.weixin.qq.com/wiki/doc/apiv3/index.shtml)
:::

## 公众号支付

```php
Pay::config($config);

$order = [
    'out_trade_no' => time().'', //需为 string 类型
    'description' => 'subject-测试',
    'amount' => [
        'total' => 1,
    ],
    'payer' => [
        'openid' => 'onkVf1FjWS5SBxxxxxxxx',
    ],
];

$result = Pay::wechat()->mp($order);
// 返回 Collection 实例。包含了调用 JSAPI 的所有参数，如appId，timeStamp，nonceStr，package，signType，paySign 等；
// 可直接通过 $result->appId, $result->timeStamp 获取相关值。
// 后续调用不在本文档讨论范围内，请自行参考官方文档。
```

## H5支付

```php
Pay::config($config);

$order = [
    'out_trade_no' => time().'',
    'description' => 'subject-测试',
    'amount' => [
        'total' => 1,
    ],
    'scene_info' => [
        'payer_client_ip' => '1.2.4.8',
        'h5_info' => [
            'type' => 'Wap',
        ]
    ],
];

return Pay::wechat()->h5($order);
```

## APP 支付

```php
Pay::config($config);

$order = [
    'out_trade_no' => time().'',
    'description' => 'subject-测试',
    'amount' => [
        'total' => 1,
    ],
];

// 将返回 json 格式，供后续 APP 调用，调用方式不在本文档讨论范围内，请参考官方文档。
return Pay::wechat()->app($order);
```

## 小程序支付

```php
Pay::config($config);

$order = [
    'out_trade_no' => time().'',
    'description' => 'subject-测试',
    'amount' => [
        'total' => 1,
        'currency' => 'CNY',
    ],
    'payer' => [
        'openid' => '123fsdf234',
    ]
];

$result = Pay::wechat()->mini($order);
// 返回 Collection 实例。包含了调用 JSAPI 的所有参数，如appId，timeStamp，nonceStr，package，signType，paySign 等；
// 可直接通过 $result->appId, $result->timeStamp 获取相关值。
// 后续调用不在本文档讨论范围内，请自行参考官方文档。
```

## 刷卡支付（付款码，被扫码）

```php
Pay::config($config);

$order = [
    'description' => '测试 - yansongda - 1',
    'out_trade_no' => time().'',
    'payer' => [
        'auth_code' => 'xxxxxxxxxxx'
    ],
    'amount' => [
        'total' => 1,
    ],
    'scene_info' => [
        'id' => '5678'
    ],
];

$result = Pay::wechat()->pos($order);
```

## 扫码支付

```php
Pay::config($config);

$order = [
    'out_trade_no' => time().'',
    'description' => 'subject-测试',
    'amount' => [
        'total' => 1,
    ],
];

$result = Pay::wechat()->scan($order);
// 二维码内容： $qr = $result->code_url;
```

## 转账

即，商家转账到零钱

```php
Pay::config($config);

$order = [
    'out_batch_no' => time().'',
    'batch_name' => 'subject-测试',
    'batch_remark' => 'test',
    'total_amount' => 1,
    'total_num' => 1,
    'transfer_detail_list' => [
        [
            'out_detail_no' => time().'-1',
            'transfer_amount' => 1,
            'transfer_remark' => 'test',
            'openid' => 'MYE42l80oelYMDE34nYD456Xoy',
            // 'user_name' => '闫嵩达'  // 明文传参即可，sdk 会自动加密
        ],
    ],
];

$result = Pay::wechat()->transfer($order);
```

## 退款

```php
Pay::config($config);

$order = [
    // app，h5，jsapi，native，mini，退款在微信支付中，都是统一的接口，可以不用指定
    // 但是，如果你想指定，可以使用 _action 参数
    // 注意，合单退款，需要指定 _action 为 combine
    // '_action' => 'app', // 指定 app 退款
    // '_action' => 'combine', // 指定 合单 退款
    // '_action' => 'h5', // 指定 h5 退款
    // '_action' => 'jsapi', // 指定 jsapi 退款，默认
    // '_action' => 'mini', // 指定 小程序 退款
    // '_action' => 'native', // 指定 native 退款
    'out_refund_no' => time().'',
    'reason' => 'test',
    'amount' => [
        'refund' => 1,
        'total' => 1,
        'currency' => 'CNY',
    ],
];

$result = Pay::wechat()->refund($order);
```

## 查询订单

```php
Pay::config($config);

$order = [
    'out_trade_no' => '123456789',
];

$result = Pay::wechat()->query($order);

// 查询退款订单
$order = [
    '_action' => 'refund',
    'out_refund_no' => '123456789',
];
$result = Pay::wechat()->query($order);

// 查询合单订单
$order = [
    '_action' => 'combine',
    'combine_out_trade_no' => '123456789',
];
$result = Pay::wechat()->query($order);

// 查询转账订单
$order = [
    '_action' => 'transfer',
    'out_batch_no' => '123456789',
    'out_detail_no' => '123456789',
];
$result = Pay::wechat()->query($order);
```

## 微信回调处理

```php
Pay::config($this->config);

$result = Pay::wechat()->callback();
```

## 响应微信回调

```php
Pay::config($this->config);

return Pay::wechat()->success();
```


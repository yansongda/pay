# 微信支付

微信支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

| method |   说明   |      参数      |    返回值     |
|:------:|:------:|:------------:|:----------:|
|   mp   | 公众号支付  | array $order | Collection |
|  wap   | 手机网站支付 | array $order | Collection |
|  app   | APP 支付 | array $order | Collection |
|  scan  |  扫码支付  | array $order | Collection |
|  mini  | 小程序支付  | array $order | Collection |

## 公众号支付

### 例子

```php
Pay::config($config);

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

$result = Pay::wechat()->mp($order);
// 返回 Collection 实例。包含了调用 JSAPI 的所有参数，如appId，timeStamp，nonceStr，package，signType，paySign 等；
// 可直接通过 $result->appId, $result->timeStamp 获取相关值。
// 后续调用不在本文档讨论范围内，请自行参考官方文档。
```

:::warning 调起微信支付 timeStamp 参数问题
[微信支付](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_4.shtml) 和 [微信](https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html#58) 两个文档所需要的参数不一致，微信支付中是 timeStamp, 微信调起的参数是 timestamp，需要自行处理。
:::

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`appid`，`sign` 等参数，大家只需传入订单类主观参数即可。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_1_1.shtml)，查看「请求参数」一栏。


## 手机网站支付

### 例子

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

return Pay::wechat()->wap($order);
// $result->h5_url;
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`trade_type`，`appid`，`sign` 等参数，大家只需传入订单类主观参数即可。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_3_1.shtml)，查看「请求参数」一栏。

### 调用支付

后续调起支付不再本文档讨论范围内，请参考[官方文档](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_3_4.shtml)

### 其它

#### 使用小程序的 app_id 关联 h5 支付

默认情况下，H5 支付所使用的 appid 是微信公众号的 appid，即配置文件中的 mp_app_id 参数，如果想使用关联的小程序的 appid，则只需要在调用参数中增加 `['_type' => 'mini']` 即可，例如：

```php
$order = [
    '_type' => 'mini', // 注意这一行
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
```

## APP 支付

### 例子

```php
Pay::config($config);

$order = [
    'out_trade_no' => time().'',
    'description' => 'subject-测试',
    'amount' => [
        'total' => 1,
    ],
];

// 将返回 Collection 实例，供后续 APP 调用，调用方式不在本文档讨论范围内，请参考官方文档。
return Pay::wechat()->app($order);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`trade_type`，`appid`，`sign` 等参数，大家只需传入订单类主观参数即可。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_2_1.shtml)，查看「请求参数」一栏。

### 调用支付

后续调起支付不再本文档讨论范围内，请参考[官方文档](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_2_4.shtml)

## 扫码支付

### 例子

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

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`trade_type`，`appid`，`sign` 等参数，大家只需传入订单类主观参数即可。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_4_1.shtml)，查看「请求参数」一栏。

### 调用支付

后续调起支付不再本文档讨论范围内，请参考[官方文档](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_4_4.shtml)

## 小程序

### 例子

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

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`trade_type`，`appid`，`sign` 等参数，大家只需传入订单类主观参数即可。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_5_1.shtml)，查看「请求参数」一栏。

### 调用支付

后续调起支付不再本文档讨论范围内，请参考[官方文档](https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter3_5_4.shtml)

## 账户转账

### 例子

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

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`appid`，`sign` 等参数，大家只需传入订单类主观参数即可。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/wiki/doc/apiv3/wxpay/pay/transfer/chapter3_1.shtml)，查看「请求参数」一节。


## 刷卡支付（付款码，被扫码）

:::warning
微信支付 v3 版 api 并不支持刷卡支付，后续将接入微信支付 v2 版 API，敬请期待。如果确实有此需求，可以使用 [Pay 的 v2 版](/docs/v2/wechat/)
:::

## 普通红包

:::warning
微信支付 v3 版 api 并不支红包，后续将接入微信支付 v2 版 API，敬请期待。如果确实有此需求，可以使用 [Pay 的 v2 版](/docs/v2/wechat/)
:::

## 裂变红包

:::warning
微信支付 v3 版 api 并不支持红包，后续将接入微信支付 v2 版 API，敬请期待。如果确实有此需求，可以使用 [Pay 的 v2 版](/docs/v2/wechat/)
:::

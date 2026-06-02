# 微信小程序虚拟支付

微信小程序虚拟支付支持小程序内虚拟商品（如游戏代币、会员订阅等）的支付能力。

:::tip 官方文档
- [虚拟支付概述](https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html)
- [服务端 API](https://developers.weixin.qq.com/miniprogram/dev/server/API/VirtualPayment/overview.html)
:::

## 配置

在微信配置中增加 `virtual_pay` 参数：

```php
$config = [
    'wechat' => [
        'default' => [
            'mch_id' => '',
            'mch_secret_key' => '',
            'mch_secret_cert' => '',
            'mch_public_cert_path' => '',
            'mini_app_id' => 'wx***', // 必填，虚拟支付必须使用小程序 app_id
            'virtual_pay' => [
                // 必填 - 商户号（offer_id）
                'offer_id' => '1234567890',
                // 必填 - 签名密钥（客户端签名、服务端 API 签名）
                'app_key' => 'your_app_key',
                // 选填 - 沙箱环境签名密钥
                'sandbox_app_key' => 'your_sandbox_app_key',
                // 必填 - 回调验签 Token
                'callback_token' => 'your_callback_token',
                // 必填 - 回调解密密钥（43 字符）
                'encoding_aes_key' => 'your_encoding_aes_key',
            ],
        ],
    ],
];

Pay::config($config);
```

:::warning
虚拟支付必须配置 `mini_app_id`，因为虚拟支付仅支持小程序场景。
:::

## 客户端签名（代币充值）

客户端签名用于小程序端调起虚拟支付，SDK 会生成签名数据供前端使用，**不会发送 HTTP 请求**。

|   方法名   |      参数      |    返回值     |
|:--------:|:------------:|:----------:|
|  virtual | array $order | Collection |

### 例子

```php
Pay::config($config);

$order = [
    'buyQuantity' => 100,
    'productId' => 'product_001',
    'goodsPrice' => 1,
    'outTradeNo' => time().'',
    'currencyType' => 'CNY', // 可选，默认 CNY
    'attach' => '自定义数据', // 可选
];

$result = Pay::wechat()->virtual($order);
// 返回 Collection 实例，包含以下字段：
// $result->offerId       - 商户号
// $result->buyQuantity   - 购买数量
// $result->currencyType  - 货币类型
// $result->productId     - 商品 ID
// $result->goodsPrice    - 商品价格（分）
// $result->outTradeNo    - 商户订单号
// $result->paySig        - 签名
// $result->env           - 环境（0=正式，1=沙箱）
```

### 订单配置参数

| 参数 | 必填 | 说明 |
|------|:----:|------|
| buyQuantity | ✅ | 购买数量 |
| productId | ✅ | 商品 ID |
| goodsPrice | ✅ | 商品价格（单位：分） |
| outTradeNo | ✅ | 商户订单号 |
| currencyType | ❌ | 货币类型，默认 `CNY` |
| attach | ❌ | 自定义数据 |
| env | ❌ | 环境：`0`=正式（默认），`1`=沙箱 |

:::tip
客户端签名场景下，SDK 返回签名数据后，需要在小程序端调用 `wx.requestVirtualPayment()` 完成支付。
:::

### 使用沙箱环境

```php
$order = [
    'buyQuantity' => 100,
    'productId' => 'product_001',
    'goodsPrice' => 1,
    'outTradeNo' => time().'',
    'env' => 1, // 沙箱环境
];

$result = Pay::wechat()->virtual($order);
```

### 传递 session_key

如果需要验证用户身份，可以传递 `_session_key` 参数：

```php
$order = [
    'buyQuantity' => 100,
    'productId' => 'product_001',
    'goodsPrice' => 1,
    'outTradeNo' => time().'',
    '_session_key' => '用户的 session_key',
];

$result = Pay::wechat()->virtual($order);
// 返回结果中会额外包含 signature 字段
```

## 服务端 API

服务端 API 用于查询、退款等操作，SDK 会发送 HTTP 请求到微信服务器。

### 查询用户代币余额

```php
$order = [
    'openid' => '用户的 openid',
    'env' => 0, // 0=正式，1=沙箱
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Currency\QueryBalancePlugin::class],
    $order
);
```

### 代币扣减

```php
$order = [
    'openid' => '用户的 openid',
    'user_ip' => '用户 IP',
    'amount' => 100,
    'order_id' => '订单号',
    'payitem' => '道具信息',
    'remark' => '备注',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Currency\CurrencyPayPlugin::class],
    $order
);
```

### 赠送代币

```php
$order = [
    'openid' => '用户的 openid',
    'amount' => 100,
    'order_id' => '订单号',
    'remark' => '备注',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Currency\PresentCurrencyPlugin::class],
    $order
);
```

### 撤销代币扣减

```php
$order = [
    'openid' => '用户的 openid',
    'order_id' => '订单号',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Currency\CancelCurrencyPayPlugin::class],
    $order
);
```

### 查询订单

```php
$order = [
    'openid' => '用户的 openid',
    'env' => 0,
    'order_id' => '商户订单号', // 与 wx_order_id 二选一
    // 'wx_order_id' => '微信订单号',
];

$result = Pay::wechat()->query([
    '_action' => 'virtual',
    ...$order,
]);
```

### 退款

```php
$order = [
    'openid' => '用户的 openid',
    'order_id' => '商户订单号', // 与 wx_order_id 二选一
    'refund_order_id' => '退款单号',
    'left_fee' => 100,
    'refund_fee' => 50,
    'refund_reason' => '用户申请退款',
    'req_from' => '退款来源',
];

$result = Pay::wechat()->refund([
    '_action' => 'virtual',
    ...$order,
]);
```

### 通知发货

```php
$order = [
    'order_id' => '商户订单号', // 与 wx_order_id 二选一
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Order\NotifyProvideGoodsPlugin::class],
    $order
);
```

### 批量上传道具

```php
$order = [
    'group_id' => '分组 ID',
    'goods_list' => [
        ['goods_id' => 'goods_001', 'goods_name' => '道具名称', ...],
    ],
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Goods\StartUploadGoodsPlugin::class],
    $order
);
```

### 查询上传道具结果

```php
$order = [
    'group_id' => '分组 ID',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Goods\QueryUploadGoodsPlugin::class],
    $order
);
```

### 批量发布道具

```php
$order = [
    'group_id' => '分组 ID',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Goods\StartPublishGoodsPlugin::class],
    $order
);
```

### 查询发布道具结果

```php
$order = [
    'group_id' => '分组 ID',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Goods\QueryPublishGoodsPlugin::class],
    $order
);
```

### 预通知扣款（订阅）

```php
$order = [
    'openid' => '用户的 openid',
    'contract_id' => '合约 ID',
    'pre_payment_amount' => 100,
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\SendSubscribePrePaymentPlugin::class],
    $order
);
```

### 确认扣款（订阅）

```php
$order = [
    'openid' => '用户的 openid',
    'contract_id' => '合约 ID',
    'pre_payment_id' => '预支付单号',
    'pre_payment_amount' => 100,
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\SubmitSubscribePayOrderPlugin::class],
    $order
);
```

### 查询订阅合约

```php
$order = [
    'openid' => '用户的 openid',
    'contract_id' => '合约 ID',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\QuerySubscribeContractPlugin::class],
    $order
);
```

### 取消订阅合约

```php
$order = [
    'openid' => '用户的 openid',
    'contract_id' => '合约 ID',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Subscribe\CancelSubscribeContractPlugin::class],
    $order
);
```

### 创建提现单

```php
$order = [
    'withdraw_no' => '提现单号',
    'withdraw_amount' => 100, // 选填
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\CreateWithdrawOrderPlugin::class],
    $order
);
```

### 查询提现单

```php
$order = [
    'withdraw_no' => '提现单号',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\QueryWithdrawOrderPlugin::class],
    $order
);
```

### 查询商户余额

```php
$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Withdraw\QueryBizBalancePlugin::class],
    []
);
```

### 下载账单

```php
$order = [
    'bill_date' => '2024-01-01',
    'bill_type' => 'ALL',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Order\DownloadBillPlugin::class],
    $order
);
```

### 查询下载结果

```php
$order = [
    'download_bill_id' => '下载单号',
];

$result = Pay::wechat()->pay(
    [\Yansongda\Pay\Plugin\Wechat\Virtual\Order\QueryDownloadOrderPlugin::class],
    $order
);
```

## 接收回调

虚拟支付回调使用与普通微信回调不同的签名和加密方式。

### 例子

```php
Pay::config($config);

$result = Pay::wechat()->callback(null, ['_action' => 'virtual']);
// 返回 Collection 实例，包含解密后的回调数据
```

:::warning
虚拟支付回调**必须**传递 `_action => 'virtual'` 参数，否则会使用 V3 回调插件导致验签失败。
:::

### 回调事件类型

虚拟支付回调包含以下事件类型：

| 事件 | 说明 |
|------|------|
| xpay_goods_deliver_notify | 道具发货通知 |
| xpay_coin_pay_notify | 代币支付通知 |
| xpay_refund_notify | 退款通知 |

### 回调数据示例

```php
$result = Pay::wechat()->callback(null, ['_action' => 'virtual']);

// 通用字段
$result->ToUserName;  // 公众号/小程序原始 ID
$result->FromUserName; // 用户 openid
$result->CreateTime;   // 创建时间
$result->MsgType;      // 消息类型（event）
$result->Event;        // 事件类型

// 业务字段（根据事件类型不同）
$result->OrderKey;     // 订单信息（代币支付通知）
$result->RefundId;     // 退款单号（退款通知）
```

## 确认回调

|    方法名     | 参数  |   返回值    |
|:----------:|:---:|:--------:|
| virtualSuccess |  无  | Response |

### 例子

```php
Pay::config($config);

$result = Pay::wechat()->callback(null, ['_action' => 'virtual']);

// 处理业务逻辑...

return Pay::wechat()->virtualSuccess();
```

### 响应格式

默认返回 XML 格式：

```xml
<xml>
  <ErrCode>0</ErrCode>
  <ErrMsg>success</ErrMsg>
</xml>
```

如果需要返回 JSON 格式：

```php
return Pay::wechat()->virtualSuccess('json');
// {"ErrCode":0,"ErrMsg":"success"}
```

:::tip
微信虚拟支付回调的响应格式与普通支付不同，使用 `ErrCode`/`ErrMsg` 而非 `code`/`message`。
:::

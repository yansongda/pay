# 微信更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自有的使用任何内置插件和自定义插件调用支付宝的任何 API。

诸如签名、API调用、解密、验签、解包等基础插件已经内置在 Pay 中，
您可以使用 `Pay::wechat()->mergeCommonPlugins(array $plugins)` 来获取调用 API 所必须的常用插件

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'transaction_id' => '1217752501201407033233368018',
];

$allPlugins = Pay::wechat()->mergeCommonPlugins([QueryPlugin::class]);

$result = Pay::wechat()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 支付产品

### JSAPI 支付

- JSAPI下单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\PayPlugin`

  :::warning 注意
  一般配合 `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\InvokePlugin` 使用
  :::

- JSAPI调起支付

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\InvokePlugin`

- 微信支付订单号查询订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\QueryByWxPlugin`

- 商户订单号查询订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\QueryPlugin`

- 关闭订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\ClosePlugin`

- 退款申请

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\RefundPlugin`

- 查询单笔退款（通过商户退款单号）

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\QueryRefundPlugin`

- 申请交易账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\GetTradeBillPlugin`

- 申请资金账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\GetFundBillPlugin`

- 下载账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Jsapi\DownloadBillPlugin`


### APP 支付

- App下单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\PayPlugin`

  :::warning 注意
  一般配合 `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\InvokePlugin` 使用
  :::

- App调起支付

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\InvokePlugin`

- 微信支付订单号查询订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\QueryByWxPlugin`

- 商户订单号查询订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\QueryPlugin`

- 关闭订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\ClosePlugin`

- 退款申请

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\RefundPlugin`

- 查询单笔退款（通过商户退款单号）

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\QueryRefundPlugin`

- 申请交易账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\GetTradeBillPlugin`

- 申请资金账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\GetFundBillPlugin`

- 下载账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\App\DownloadBillPlugin`


### H5 支付

- H5下单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\PayPlugin`

- 微信支付订单号查询订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\QueryByWxPlugin`

- 商户订单号查询订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\QueryPlugin`

- 关闭订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\ClosePlugin`

- 退款申请

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\RefundPlugin`

- 查询单笔退款（通过商户退款单号）

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\QueryRefundPlugin`

- 申请交易账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\GetTradeBillPlugin`

- 申请资金账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\GetFundBillPlugin`

- 下载账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\H5\DownloadBillPlugin`


### Native 支付

- Native下单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\PayPlugin`

- 微信支付订单号查询订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\QueryByWxPlugin`

- 商户订单号查询订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\QueryPlugin`

- 关闭订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\ClosePlugin`

- 退款申请

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\RefundPlugin`

- 查询单笔退款（通过商户退款单号）

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\QueryRefundPlugin`

- 申请交易账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\GetTradeBillPlugin`

- 申请资金账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\GetFundBillPlugin`

- 下载账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Native\DownloadBillPlugin`


### 小程序支付

- 小程序下单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\PayPlugin`

  :::warning 注意
  一般配合 `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\InvokePlugin` 使用
  :::

- 小程序调起支付

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\InvokePlugin`

- 微信支付订单号查询订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\QueryByWxPlugin`

- 商户订单号查询订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\QueryPlugin`

- 关闭订单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\ClosePlugin`

- 退款申请

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\RefundPlugin`

- 查询单笔退款（通过商户退款单号）

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\QueryRefundPlugin`

- 申请交易账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\GetTradeBillPlugin`

- 申请资金账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\GetFundBillPlugin`

- 下载账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Mini\DownloadBillPlugin`


### 合单支付

- 合单下单-JSAPI

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\JsapiPayPlugin`

  :::warning 注意
  一般配合 `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\JsapiInvokePlugin` 使用
  :::

- 合单下单-APP

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\AppPayPlugin`

  :::warning 注意
  一般配合 `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\AppInvokePlugin` 使用
  :::

- 合单下单-H5

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\H5PayPlugin`

- 合单下单-Native

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\NativePayPlugin`

- 合单下单-小程序

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\MiniPayPlugin`

  :::warning 注意
  一般配合 `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\MiniInvokePlugin` 使用
  :::

- 合单查询

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\QueryPlugin`

- 合单关单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\ClosePlugin`

- 退款申请

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\RefundPlugin`

- 查询单笔退款（通过商户退款单号）

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\QueryRefundPlugin`

- 申请交易账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\GetTradeBillPlugin`

- 申请资金账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\GetFundBillPlugin`

- 下载账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Combine\DownloadBillPlugin`


### 资金/交易账单

- 申请交易账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Bill\GetTradePlugin`

- 申请资金账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Bill\GetFundPlugin`

- 下载账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Bill\DownloadPlugin`

### 退款

- 退款申请

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Refund\RefundPlugin`

- 查询单笔退款（通过商户退款单号）

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Refund\QueryPlugin`

- 发起异常退款

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Refund\RefundAbnormalPlugin`

## 委托代扣

[文档](https://pay.weixin.qq.com/wiki/doc/api/wxpay_v2/papay/chapter1_1.shtml)

### 只签约

> 具体签约相关参数，请参阅委托代扣的文档

```php
Pay::config($config);

$result = Pay::wechat()->papay([
    '_action' => 'contract',
    '_type' => 'mini', // 通过小程序签约
    'contract_code' => '我方签约号',
    'contract_display_account' => '签约人',
    'mch_id' => '商户号',
    'notify_url' => '签约成功回调地址',
    'plan_id' => '委托代扣后台创建的模板ID',
    'request_serial' => '请求序列号',
    'timestamp' => time(),
    'outerid' => '我方用户ID',
])->toArray();
```

### 支付中签约

> 具体签约相关参数，请参阅委托代扣的文档

```php
Pay::config($config);

$result = Pay::wechat()->papay([
    '_type' => 'mini',
    'contract_mchid' => '签约商户ID',
    'contract_appid' => '签约AppID',
    'out_trade_no' => '我方订单号',
    'body' => '委托代扣',
    'notify_url' => '支付回调地址',
    'total_fee' => 1000,
    'spbill_create_ip' => '127.0.0.1',
    'trade_type' => 'JSAPI',
    'plan_id' => '委托代扣后台创建的模板ID',
    'openid' => '用户OpenID',
    'contract_code' => "我方签约号",
    'request_serial' => '请求序列号',
    'contract_display_account' => '签约人',
    'contract_notify_url' => '签约成功回调地址',
])->toArray();
```

### 代扣

> 具体代扣相关参数，请参阅委托代扣的文档

```php
Pay::config($config);

$result = Pay::wechat()->papay([
    '_action' => 'apply',
    '_type' => 'mini',
    'body' => '委托代扣',
    'out_trade_no' => '我方订单号',
    'total_fee' => 1000,
    'spbill_create_ip' => '127.0.0.1',
    'notify_url' => '代扣成功回调地址',
    'contract_id' => '签约ID',
])->toArray();
```

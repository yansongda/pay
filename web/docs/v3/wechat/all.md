# 微信更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自有的使用任何内置插件和自定义插件调用微信的任何 API。

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

  :::warning 注意
  此插件不需要验证微信签名，即，一共只需要 `[StartPlugin::class, DownloadBillPlugin::class, AddPayloadBodyPlugin::class, AddPayloadSignaturePlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class]` 插件
  :::

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

  :::warning 注意
  此插件不需要验证微信签名，即，一共只需要 `[StartPlugin::class, DownloadBillPlugin::class, AddPayloadBodyPlugin::class, AddPayloadSignaturePlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class]` 插件
  :::

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

  :::warning 注意
  此插件不需要验证微信签名，即，一共只需要 `[StartPlugin::class, DownloadBillPlugin::class, AddPayloadBodyPlugin::class, AddPayloadSignaturePlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class]` 插件
  :::

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

  :::warning 注意
  此插件不需要验证微信签名，即，一共只需要 `[StartPlugin::class, DownloadBillPlugin::class, AddPayloadBodyPlugin::class, AddPayloadSignaturePlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class]` 插件
  :::

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

  :::warning 注意
  此插件不需要验证微信签名，即，一共只需要 `[StartPlugin::class, DownloadBillPlugin::class, AddPayloadBodyPlugin::class, AddPayloadSignaturePlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class]` 插件
  :::

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

  :::warning 注意
  此插件不需要验证微信签名，即，一共只需要 `[StartPlugin::class, DownloadBillPlugin::class, AddPayloadBodyPlugin::class, AddPayloadSignaturePlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class]` 插件
  :::

### 资金/交易账单

- 申请交易账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Bill\GetTradePlugin`

- 申请资金账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Bill\GetFundPlugin`

- 下载账单

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Bill\DownloadPlugin`

  :::warning 注意
  此插件不需要验证微信签名，即，一共只需要 `[StartPlugin::class, DownloadBillPlugin::class, AddPayloadBodyPlugin::class, AddPayloadSignaturePlugin::class, AddRadarPlugin::class, ResponsePlugin::class, ParserPlugin::class]` 插件
  :::

### 退款

- 退款申请

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Refund\RefundPlugin`

- 查询单笔退款（通过商户退款单号）

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Refund\QueryPlugin`

- 发起异常退款

  `\Yansongda\Pay\Plugin\Wechat\V3\Pay\Refund\RefundAbnormalPlugin`

  :::warning 注意
  传递明文即可，内部会自动加密
  :::

## 运营工具

### 商家转账到零钱

- 发起商家转账

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\CreatePlugin`

#### 查询转账批次单

- 通过微信批次单号查询批次单

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\Batch\QueryByWxPlugin`

- 通过商家批次单号查询批次单

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\Batch\QueryPlugin`

#### 查询转账明细单

- 通过微信明细单号查询明细单

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\Detail\QueryByWxPlugin`

- 通过商家明细单号查询明细单

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\Detail\QueryPlugin`

#### 申请转账电子回单

- 转账账单电子回单申请受理接口

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\Receipt\CreatePlugin`

- 查询转账账单电子回单接口

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\Receipt\QueryPlugin`

#### 申请转账明细电子回单

- 受理转账明细电子回单API

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\ReceiptDetail\CreatePlugin`

- 查询转账明细电子回单受理结果API

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\ReceiptDetail\QueryPlugin`

- 下载电子回单

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Transfer\DownloadReceiptPlugin`

### 平台收付通（余额查询）

- 查询电商平台账户实时余额API

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceBalance\QueryPlugin`

- 查询电商平台账户日终余额API

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceBalance\QueryDayEndPlugin`

### 平台收付通（退款）

- 申请退款

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund\ApplyPlugin`

- 查询单笔退款（按微信支付退款单号）

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund\QueryByWxPlugin`

- 查询单笔退款（按商户退款单号）

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund\QueryPlugin`

- 查询垫付回补结果

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund\QueryReturnAdvancePlugin`

- 垫付退款回补

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\ECommerceRefund\ReturnAdvancePlugin`

### 代金券

#### 批次

- 创建代金券批次

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\CreatePlugin`

- 激活代金券批次

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\StartPlugin`

- 暂停代金券批次

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\PausePlugin`

- 重启代金券批次

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\RestartPlugin`

- 条件查询批次列表

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\QueryPlugin`

- 查询批次详情

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\QueryDetailPlugin`

- 查询代金券可用商户

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\QueryMerchantsPlugin`

- 查询代金券可用单品

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\QueryItemsPlugin`

- 下载批次退款明细

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\QueryRefundFlowPlugin`

- 下载批次核销明细

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Stock\QueryUseFlowPlugin`

#### 代金券

- 根据商户号查用户的券

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Coupons\QueryUserPlugin`

- 发放指定批次的代金券

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Coupons\SendPlugin`

- 查询代金券详情

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Coupons\DetailPlugin`

#### 消息通知地址

- 查询消息通知地址

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Callback\QueryPlugin`

- 设置消息通知地址

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Coupon\Callback\SetPlugin`

### 电子发票

#### 公共API

- 创建电子发票卡券模板

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\CreateCardTemplatePlugin`

- 配置开发选项

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\UpdateConfigPlugin`

- 查询商户配置的开发选项

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\QueryConfigPlugin`

- 查询电子发票

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\QueryPlugin`

- 获取抬头填写链接

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\GetTitleUrlPlugin`

- 获取用户填写的抬头

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\QueryUserTitlePlugin`

#### 区块链电子发票

- 获取商户开票基础信息

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\GetBaseInformationPlugin`

- 获取商户可开具的商品和服务税收分类编码对照表

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\GetTaxCodePlugin`

- 开具电子发票

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\CreatePlugin`

- 冲红电子发票

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\ReversePlugin`

- 获取发票下载信息

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\GetDownloadInfoPlugin`

- 下载发票文件

  `\Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao\Blockchain\DownloadPlugin`

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

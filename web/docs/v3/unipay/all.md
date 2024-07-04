# 银联更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自由的使用任何内置插件和自定义插件调用银联的任何 API。

诸如签名、API调用、解密、验签、解包等基础插件已经内置在 Pay 中，
您可以使用 `Pay::unipay()->mergeCommonPlugins(array $plugins)` 来获取调用 API 所必须的常用插件

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'out_trade_no' => '1514027114',
];

$allPlugins = Pay::unipay()->mergeCommonPlugins([QueryPlugin::class]);

$result = Pay::unipay()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 银联开放平台 - 在线网关支付

- 消费接口

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\Web\PayPlugin`

- 消费撤销接口

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\Web\CancelPlugin`

- 退货接口

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\Web\RefundPlugin`

- 交易状态查询接口

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\Web\QueryPlugin`

## 银联开放平台 - H5 支付

- 消费接口

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\H5\PayPlugin`

## 银联开放平台 - 二维码支付

- 交易状态查询接口

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\QueryPlugin`

- 申请二维码（主扫）

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPlugin`

- 申请预授权二维码（主扫）

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPreAuthPlugin`

- 统一下单（主扫）

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanPreOrderPlugin`

- 申请缴费二维码

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\ScanFeePlugin`

- 维码预授权（被扫）

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\PosPreAuthPlugin`

- 二维码消费（被扫）

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\PosPlugin`

- 退货类（退货）

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\RefundPlugin`

- 退货类（消费撤销类）

  `\Yansongda\Pay\Plugin\Unipay\Open\Pay\QrCode\CancelPlugin`

## QRA 平台 - 刷卡支付

- 提交被扫支付API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Pos\PayPlugin`

- 查询订单API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Pos\QueryPlugin`

- 撤销订单API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Pos\CancelPlugin`

- 申请退款API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Pos\RefundPlugin`

- 退款查询API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Pos\QueryRefundPlugin`

- 授权码查询openid API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Pos\QueryOpenIdPlugin`

## QRA 平台 - 扫码支付

- 统一下单API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Scan\PayPlugin`

- 查询订单API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Scan\QueryPlugin`

- 关闭订单API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Scan\ClosePlugin`

- 申请退款API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Scan\RefundPlugin`

- 查询退款API

  `\Yansongda\Pay\Plugin\Unipay\Qra\Scan\QueryRefundPlugin`

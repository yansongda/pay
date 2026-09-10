# 翼支付

中国电信翼支付商户开放平台（MAPI）。

> **金额单位为「分」**，与其他渠道习惯不同，请注意换算。

## 配置

```php
use Yansongda\Pay\Pay;

$config = [
    'bestpay' => [
        'default' => [
            // 必填-商户号（自动注入 bizContent.merchantNo，下单时可覆盖）
            'merchant_no' => '3178033925245778',
            // 必填-机构号（commonParams.institutionCode）
            'institution_code' => '3178033925245778',
            // 选填-机构类型，默认 MERCHANT
            'institution_type' => 'MERCHANT',
            // 必填-商户 PKCS12 证书路径
            'mch_secret_cert_path' => '/path/bestpay.p12',
            // 必填-P12 密码
            'mch_secret_cert_password' => '******',
            // 必填-翼支付平台公钥证书路径（响应/回调验签）
            'bestpay_public_cert_path' => '/path/bestpay.cer',
            // 选填-接口版本，默认 1.0.3
            'api_version' => '1.0.3',
            // 选填
            'notify_url' => 'https://yansongda.cn/bestpay/notify',
            'return_url' => 'https://yansongda.cn/bestpay/return',
            // 选填-MODE_NORMAL / MODE_SANDBOX
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
];

Pay::config($config);
```

## 支付

```php
// PC 收银台（超级收银台 tradeCreate，tradeChannel=WEBCASHIER）
$result = Pay::bestpay()->web([
    'outTradeNo' => '1514027114',
    'tradeAmt' => '1', // 单位：分
    'ccy' => '156', // 人民币，默认 156
    'subject' => 'yansongda 测试',
    'goodsInfo' => 'yansongda 测试',
    'operator' => '3178033925245778', // 操作员，常与商户号相同
    'mediumType' => 'WIRELESS', // 媒介类型（以商户开通产品联调为准）
    'requestDate' => date('Y-m-d H:i:s'), // 有效范围约 T-1 ~ T+1 天
]);

// 手机收银台（tradeChannel=MOBILECASHIER）
$result = Pay::bestpay()->h5([/* 同上 */]);

// 聚合收款码（线下聚合，字段以商户开通产品为准）
$result = Pay::bestpay()->scan([
    'outTradeNo' => '1514027114',
    'tradeAmt' => '1',
    'subject' => 'yansongda 测试',
    // ...
]);
```

> `merchantNo` 已从配置 `merchant_no` 自动注入，如需覆盖可在下单参数中显式传入。

## 查询 / 退款 / 关单

```php
// 订单查询（默认：超级收银台 /integrate/orderQuery）
Pay::bestpay()->query([
    'outTradeNo' => '1514027114',
]);

// 订单查询（线下聚合 1006 订单：/aggregate/aggregatepay/tradeQuery）
Pay::bestpay()->query([
    'outTradeNo' => '1514027114',
    '_action' => 'aggregate',
]);

// 退款
Pay::bestpay()->refund([
    'outTradeNo' => '1514027114',
    'outRequestNo' => 'REFUND001',
    'refundAmt' => '1', // 单位：分
    'requestDate' => date('Y-m-d H:i:s'),
    // 以下字段以商户开通产品联调为准
    'originalTradeDate' => '2026-09-10',
    'operator' => '3178033925245778',
]);

// 关单
Pay::bestpay()->close([
    'outTradeNo' => '1514027114',
]);
```

## 回调

```php
$data = Pay::bestpay()->callback();

// 以 tradeStatus 判断交易结果（SUCCESS / FAIL / NOTPAY / CLOSE）
if ('SUCCESS' === $data->get('tradeStatus')) {
    // 业务处理（建议同时校验 outTradeNo 与金额）
}

return Pay::bestpay()->success();
// => {"resultCode":"SUCCESS","resultMsg":"OK"}
```

> 验签使用平台公钥（SHA1withRSA / SHA256withRSA 均尝试）；请求加签为 SHA256withRSA（PKCS12）。
> 回调处理请以翼支付官方文档 `aggregatePayOrRefundNotify` 契约为准：需处理重复通知，失败应答 `{"resultCode":"FAILED","resultMsg":"FAILED"}`。

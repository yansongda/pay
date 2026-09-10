# 翼支付

中国电信翼支付商户开放平台（MAPI）。

> **金额单位为「分」**，与其他渠道习惯不同，请注意换算。

## 配置

```php
use Yansongda\Pay\Pay;

$config = [
    'bestpay' => [
        'default' => [
            // 必填-商户号
            'merchant_no' => '3178033925245778',
            // 必填-机构号（commonParams.institutionCode）
            'institution_code' => '3178033925245778',
            // 选填-机构类型，默认 MERCHANT
            'institution_type' => 'MERCHANT',
            // 必填-商户 PKCS12 证书路径
            'mch_secret_cert_path' => '/path/bestpay.p12',
            // 必填-P12 密码
            'mch_secret_cert_password' => '******',
            // 选填-证书别名，默认 conname
            'mch_secret_cert_alias' => 'conname',
            // 必填-翼支付平台公钥证书路径（响应/回调验签）
            'bestpay_public_cert_path' => '/path/bestpay.cer',
            // 选填-接口版本，默认 1.0.3
            'api_version' => '1.0.3',
            // 选填-默认 S002
            'sign_type' => 'S002',
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
// PC 收银台
$result = Pay::bestpay()->web([
    'outTradeNo' => '1514027114',
    'tradeAmt' => '1', // 单位：分
    'subject' => 'yansongda 测试',
    'goodsInfo' => 'yansongda 测试',
    'merchantNo' => '3178033925245778',
    'operator' => '3178033925245778',
    'requestDate' => date('Y-m-d H:i:s'),
]);

// 手机收银台
$result = Pay::bestpay()->h5([/* 同上 */]);

// 聚合收款码（线下）
$result = Pay::bestpay()->scan([/* 字段以商户开通产品为准 */]);
```

## 查询 / 退款 / 关单

```php
Pay::bestpay()->query(['outTradeNo' => '1514027114']);
Pay::bestpay()->refund([
    'outTradeNo' => '1514027114',
    'outRequestNo' => 'REF001',
    'refundAmt' => '1',
]);
Pay::bestpay()->close(['outTradeNo' => '1514027114']);
```

## 回调

```php
$data = Pay::bestpay()->callback();

// 以 tradeStatus 判断交易结果
if ('SUCCESS' === $data->get('tradeStatus')) {
    // 业务处理
}

return Pay::bestpay()->success();
// => {"resultCode":"SUCCESS","resultMsg":"OK"}
```

> 验签使用平台公钥（SHA1withRSA / SHA256withRSA 均尝试）；请求加签为 SHA256withRSA（PKCS12）。

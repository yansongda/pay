# Apple（苹果支付）快速入门

在初始化完毕后，就可以直接方便的享受 `yansongda/pay` 带来的便利了。

## 配置

```php
Pay::config([
    'apple' => [
        'default' => [
            // 必填-商户 ID（App Store Connect 中创建）
            'merchant_id' => 'merchant.com.example.app',
            // 必填-支付处理证书：证书文件路径，或 PKCS#8 格式的 PEM 内容（含私钥）
            'payment_processing_cert' => '/path/to/payment_processing_cert.pem',
            // 选填-支付处理证书私钥口令
            'payment_processing_cert_passphrase' => '',
            // 选填-Apple 根证书（路径或 PEM 内容，默认内置 AppleRootCA-G3.pem）
            'apple_root_ca' => '',
            // 选填-Apple 中间证书（路径或 PEM 内容，默认内置 AppleAAICAG3.pem）
            'apple_intermediate_ca' => '',
            // 选填-App Store Server API 认证（issuer_id/bundle_id/api_key_id/api_private_key 需同时配置，仅查询/退款功能需要）
            'issuer_id' => '69a6de87-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            'bundle_id' => 'com.example.app',
            'api_key_id' => 'X5D4K9J2Q1',
            'api_private_key' => '/path/to/api.key',
            // 选填-通知地址（文档用，SDK 不主动上报）
            'notify_url' => 'https://yansongda.cn/apple/notify',
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
]);
```

:::warning
`payment_processing_cert` 支持传入证书文件路径或 PKCS#8 格式的 PEM 内容（含私钥）。P12/SEC1 格式请先转换为 PKCS#8 后再使用：

```shell
openssl pkcs8 -topk8 -nocrypt -in merchant.p12 -out merchant.pem
```

RSA_v1 支付令牌的解密需要 PHP >= 8.5（OAEP-SHA256 参数限制），EC_v1 无版本要求。
:::

## 支付令牌验签解密

```php
Pay::config($this->config);

// 注意：入参必须是数组形态，token 值支持 JSON 字符串 / 数组 / Collection
$result = Pay::apple()->payToken(['token' => $token]);
```

## App Store 查询/退款历史

```php
Pay::config($this->config);

// 单笔交易（_action 缺省为 transaction）
$result = Pay::apple()->query([
    'transaction_id' => '1234567890',
]);

// 交易历史
$result = Pay::apple()->query([
    'transaction_id' => '1234567890',
    '_action' => 'history',
]);

// 订阅状态
$result = Pay::apple()->query([
    'transaction_id' => '1234567890',
    '_action' => 'subscriptions',
]);

// 退款历史查询（Apple 无服务端主动退款）
$result = Pay::apple()->refund([
    'transaction_id' => '1234567890',
]);
```

## 接收回调

```php
Pay::config($this->config);

// 是的，你没有看错，就是这么简单！
$result = Pay::apple()->callback();
```

## Apple Pay 网页支付（merchantSession）

```php
Pay::config($this->config);

$result = Pay::apple()->merchantSession([
    'validation_url' => $url, // 前端 onvalidatemerchant 事件获取的一次性 URL（5 分钟过期）
    'display_name' => '示例商户', // 选填
    '_http' => [
        'cert' => ['/path/to/cert.pem', 'passphrase'], // TLS 双向认证证书透传
    ],
]);
```

:::warning
debug 日志会记录 payload 全量（含 `_http` 中的证书口令），生产环境请注意日志级别。
:::

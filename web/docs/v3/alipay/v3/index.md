# 支付宝 V3 API

`yansongda/pay` 现已支持支付宝 **OpenAPI V3**：RESTful 风格（`/v3/` 路径）、JSON 报文、HTTP 头签名（`ALIPAY-SHA256withRSA`）。

支付宝的 **V2 API**（网关签名 + form 表单）与 **V3 API** 通过租户配置的 `version` 字段共存：

- 未配置 `version` 或 `version` 为 `v2` 的租户，行为完全不变，仍然走 V2 网关与签名；
- `version` 配置为 `v3` 的租户，走 V3 管道（`https://openapi.alipay.com/v3/...`，`mode` 为沙箱时自动切换 V3 沙箱网关）。

本文档为 V3 API 的用法说明。V2 API 文档请见 [支付宝](/docs/v3/alipay/pay.md)。

## 支持的接口

V3 暂时支持以下服务端接口（与 V2 同名方法，调用方式不变）：

|  method  |     说明      |      参数      |    返回值    |
|:--------:|:-----------:|:------------:|:----------:|
|   pos    | 付款码支付（被扫码） | array $order | Collection |
|   scan   | 扫码支付（预创建，主动扫） | array $order | Collection |
|  query   |    交易查询     | array $order | Collection |
|  refund  |    交易退款     | array $order | Collection |
|  cancel  |    交易撤销     | array $order | Collection |
|  close   |    交易关闭     | array $order | Collection |

:::warning
以下方法在 `version` 为 `v3` 的租户上调用会抛出异常（提示通过 `_config` 指向 V2 租户），将在后续版本支持：

- 页面类接口：`web`（电脑支付）、`h5`（手机网站支付）、`app`（APP 支付）、`mini`（小程序支付）
- `transfer`（账户转账）

如需使用上述接口，请参考 [多租户逃生通道](#多租户逃生通道)。
:::

## 配置说明

在原有支付宝配置的基础上，新增以下两个字段：

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `version` | string，默认 `v2` | 租户级 API 版本，可选 `v2` / `v3`；未设置时一切如旧 |
| `alipay_public_key` | string，选填 | 支付宝公钥（**不含 PEM 头尾的纯 base64 单行**），V3 公钥模式下必填 |

:::tip
SDK 将根据 `version` 将租户配置实例化为不同的配置类：`v2` 对应 `AlipayV2Config`（网关签名），`v3` 对应 `AlipayV3Config`（OpenAPI V3）。两种配置类各自的专属字段一目了然，也便于后续按版本独立演进。
:::

### 公钥模式（推荐，配置最简）

在 [支付宝开放平台](https://open.alipay.com/develop/manage)《应用详情->开发设置->接口加签方式》中选择「公钥模式」，获取支付宝公钥：

```php
$config = [
    'alipay' => [
        'default' => [
            // 「必填」支付宝分配的 app_id
            'app_id' => '2021001178660000',
            // 「必填」应用私钥 字符串或路径
            'app_secret_cert' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAA...',
            // 「必填」支付宝公钥（不含 PEM 头尾的纯 base64 单行，非证书路径）
            'alipay_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A...',
            // 「选填」异步通知地址
            'notify_url' => 'https://yansongda.cn/alipay/notify',
            // 「必填」租户级 API 版本
            'version' => 'v3',
        ],
    ],
];
```

### 证书模式

在 V2 证书配置的基础上增加 `version` 即可（V3 协议无 `root-cert-sn`，支付宝根证书可不再配置）：

```php
$config = [
    'alipay' => [
        'default' => [
            'version' => 'v3',
            'app_id' => '2021001178660000',
            // 「必填」应用私钥 字符串或路径
            'app_secret_cert' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAA...',
            // 「必填」应用公钥证书 路径
            'app_public_cert_path' => '/Users/yansongda/pay/cert/appCertPublicKey_2021001178660000.crt',
            // 「必填」支付宝公钥证书 路径
            'alipay_public_cert_path' => '/Users/yansongda/pay/cert/alipayCertPublicKey_RSA2.crt',
            // 「选填」支付宝根证书 路径（V3 无需，多余配置会被忽略）
            'alipay_root_cert_path' => '/Users/yansongda/pay/cert/alipayRootCert.crt',
        ],
    ],
];
```

:::tip
V3 租户配置了 `app_public_cert_path` 即按证书模式校验（要求 `app_id`、`app_secret_cert`、`app_public_cert_path`、`alipay_public_cert_path` 必填）；未配置则按公钥模式校验（`app_id`、`app_secret_cert`、`alipay_public_key` 三项必填）。
:::

其余共用字段（`notify_url`、`app_auth_token`、`service_provider_id`、`mode` 等）含义与 V2 一致，请参考 [初始化](/docs/v3/quick-start/init.md)。

:::warning
当前版本暂不支持 AES 加解密（`encrypt_key`）。请勿为应用开通接口加密能力（或在配置中添加 `encrypt_key`），否则异步通知中的业务字段为密文，SDK 无法自动解密。该能力将在后续版本支持。
:::

## 多租户逃生通道

利用现有多租户机制，同一 `app_id` 可以同时配置 V3 租户（服务端接口）与 V2 租户（页面类接口），支付宝应用对 V2/V3 网关的密钥是通用的：

```php
Pay::config([
    'alipay' => [
        'default' => [                      // V3 租户：服务端六接口
            'version' => 'v3',
            'app_id' => '2021001178660000',
            'app_secret_cert' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAA...',
            'alipay_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A...',
        ],
        'page' => [                         // V2 租户：页面类接口（原有配置原样搬过来）
            'app_id' => '2021001178660000',
            'app_secret_cert' => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAA...',
            'app_public_cert_path' => '/Users/yansongda/pay/cert/appCertPublicKey_2021001178660000.crt',
            'alipay_public_cert_path' => '/Users/yansongda/pay/cert/alipayCertPublicKey_RSA2.crt',
            'alipay_root_cert_path' => '/Users/yansongda/pay/cert/alipayRootCert.crt',
        ],
    ],
]);

// V3 服务端接口：走 default 租户
Pay::alipay()->scan($order);

// V2 页面类接口：通过订单参数 `_config` 指向 V2 租户
Pay::alipay()->web(array_merge($order, ['_config' => 'page']));

// 回调：租户通过 callback() 的第二个参数传递
Pay::alipay()->callback($request, ['_config' => 'page']);
```

:::danger
租户必须通过**订单参数** `'_config' => 'xxx'`（或 `callback()` 的第二个参数）传递。`Pay::alipay(['_config' => 'page'])` 是**无效写法**：该参数会被当作配置交给 `Pay::config()`，从而被静默丢弃。
:::

## 自行实现验签相关逻辑的注意点

如果您需要自行实现与 V3 签名/验签相关的逻辑（例如自行处理同步响应验签），请注意：支付宝 V3 的签名时间戳（`Authorization` 中的 `timestamp`、同步响应头 `alipay-timestamp`）为**毫秒级（13 位）**，与微信 V3 的秒级时间戳不同。

## 升级注意

自不支持 `version` 配置的版本升级时，请检查存量支付宝配置数组中是否存在 `version` 键：

- 此前该键无对应 setter，会被静默忽略，不产生任何效果；
- 升级后 `version` 必须为**字符串** `'v2'`或 `'v3'`。若为非字符串值（如布尔值、整数），将抛出 `TypeError`；若为 `v2`/`v3` 之外的字符串，将在使用该租户时抛出配置异常。

# 配置实体类使用示例

本文档提供了使用配置实体类的完整示例。

## 基础示例

### 支付宝配置

```php
use Yansongda\Pay\Pay;
use Yansongda\Pay\Config\Config;
use Yansongda\Pay\Config\AlipayConfig;

$config = new Config(
    alipay: new AlipayConfig(
        app_id: '2016082000295641',
        app_secret_cert: 'MIIEvAIBADANBgkqhkiG9w0BAQEF...',
        app_public_cert_path: '/path/to/appCertPublicKey.crt',
        alipay_public_cert_path: '/path/to/alipayCertPublicKey.crt',
        alipay_root_cert_path: '/path/to/alipayRootCert.crt',
        return_url: 'https://yansongda.cn/alipay/return',
        notify_url: 'https://yansongda.cn/alipay/notify',
        mode: Pay::MODE_NORMAL,
    ),
);

Pay::config($config);
```

### 微信配置

```php
use Yansongda\Pay\Pay;
use Yansongda\Pay\Config\Config;
use Yansongda\Pay\Config\WechatConfig;

$config = new Config(
    wechat: new WechatConfig(
        mch_id: '1234567890',
        mch_secret_key: 'your-v3-secret-key',
        mch_secret_cert: 'your-secret-cert-content',
        mch_public_cert_path: '/path/to/apiclient_cert.pem',
        notify_url: 'https://yansongda.cn/wechat/notify',
        mp_app_id: 'wx1234567890',
        wechat_public_cert_path: [
            '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => '/path/to/wechatpay.pem',
        ],
        mode: Pay::MODE_NORMAL,
    ),
);

Pay::config($config);
```

### 完整配置示例

```php
use Yansongda\Pay\Pay;
use Yansongda\Pay\Config\Config;
use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Config\LoggerConfig;
use Yansongda\Pay\Config\HttpConfig;

$config = new Config(
    alipay: new AlipayConfig(
        app_id: '2016082000295641',
        app_secret_cert: 'your-secret-cert',
        app_public_cert_path: '/path/to/appCertPublicKey.crt',
        alipay_public_cert_path: '/path/to/alipayCertPublicKey.crt',
        alipay_root_cert_path: '/path/to/alipayRootCert.crt',
        return_url: 'https://yansongda.cn/alipay/return',
        notify_url: 'https://yansongda.cn/alipay/notify',
    ),
    wechat: new WechatConfig(
        mch_id: '1234567890',
        mch_secret_key: 'your-v3-secret-key',
        mch_secret_cert: 'your-secret-cert-content',
        mch_public_cert_path: '/path/to/apiclient_cert.pem',
        notify_url: 'https://yansongda.cn/wechat/notify',
        mp_app_id: 'wx1234567890',
    ),
    unipay: new UnipayConfig(
        mch_id: '777290058167151',
        mch_cert_path: '/path/to/unipayAppCert.pfx',
        mch_cert_password: '000000',
        unipay_public_cert_path: '/path/to/unipayCertPublicKey.cer',
        return_url: 'https://yansongda.cn/unipay/return',
        notify_url: 'https://yansongda.cn/unipay/notify',
    ),
    douyin: new DouyinConfig(
        mini_app_id: 'tt226e54d3bd581bf801',
        mch_secret_token: 'douyin_mini_token',
        mch_secret_salt: 'oDxWDBr4U7FAAQ8hnGDm29i4A6pbTMDKme4WLLvA',
        mch_id: '73744242495132490630',
        notify_url: 'https://yansongda.cn/douyin/notify',
    ),
    jsb: new JsbConfig(
        partner_id: '6a13eab71c4f4b0aa4757eda6fc59710',
        mch_secret_cert_path: '/path/to/EpayKey.pem',
        mch_public_cert_path: '/path/to/EpayCert.cer',
        jsb_public_cert_path: '/path/to/jschina.cer',
        notify_url: 'https://yansongda.cn/jsb/notify',
    ),
    logger: new LoggerConfig(
        enable: true,
        file: './logs/pay.log',
        level: 'info',
        type: 'single',
        max_file: 30,
    ),
    http: new HttpConfig(
        timeout: 5.0,
        connect_timeout: 5.0,
    ),
);

Pay::config($config);
```

## 配置转换

### 从数组转换为实体

```php
use Yansongda\Pay\Config\Config;

$arrayConfig = [
    'alipay' => [
        'default' => [
            'app_id' => '2016082000295641',
            'app_secret_cert' => 'your-secret-cert',
            'app_public_cert_path' => '/path/to/appCertPublicKey.crt',
            'alipay_public_cert_path' => '/path/to/alipayCertPublicKey.crt',
            'alipay_root_cert_path' => '/path/to/alipayRootCert.crt',
        ],
    ],
];

// 转换为实体
$config = Config::fromArray($arrayConfig);

// 访问配置
echo $config->alipay->app_id; // 输出: 2016082000295641
```

### 从实体转换为数组

```php
use Yansongda\Pay\Config\Config;
use Yansongda\Pay\Config\AlipayConfig;

$config = new Config(
    alipay: new AlipayConfig(
        app_id: '2016082000295641',
        app_secret_cert: 'your-secret-cert',
        app_public_cert_path: '/path/to/appCertPublicKey.crt',
        alipay_public_cert_path: '/path/to/alipayCertPublicKey.crt',
        alipay_root_cert_path: '/path/to/alipayRootCert.crt',
    ),
);

// 转换为数组
$array = $config->toArray();
```

## 向后兼容

配置实体类与原有的数组配置方式完全兼容，您可以继续使用数组方式配置：

```php
use Yansongda\Pay\Pay;

$config = [
    'alipay' => [
        'default' => [
            'app_id' => '2016082000295641',
            // ...其他配置
        ],
    ],
];

Pay::config($config);
```

## 优势

使用配置实体类的优势：

1. **类型安全**：IDE 可以提供自动完成和类型检查
2. **清晰明了**：必填参数和可选参数一目了然
3. **避免拼写错误**：IDE 会在编译时发现配置键名的拼写错误
4. **更好的文档**：配置类本身就是文档
5. **易于重构**：重命名配置项时 IDE 可以自动更新所有引用

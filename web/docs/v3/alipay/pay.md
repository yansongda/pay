# 支付宝支付

支付宝支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

|  method  |   说明   |      参数      |    返回值     |
|:--------:|:------:|:------------:|:----------:|
|   web    |  网页支付  | array $order |  Response  |
|    h5    | H5 支付  | array $order |  Response  |
|   app    | APP 支付 | array $order |  Response  |
|   mini   | 小程序支付  | array $order | Collection |
|   pos    |  刷卡支付  | array $order | Collection |
|   scan   |  扫码支付  | array $order | Collection |
| transfer |  账户转账  | array $order | Collection |

更多接口调用请参考后续文档

:::tip
当前 Alipay V3 支付能力分为两类：

- `web` / `h5` / `app` 仍走 gateway 兼容模式，便于继续返回表单、唤起串等前台结果；
- `mini` / `pos` / `scan` / `transfer` 已切换为官方 open-v3 REST API。

因此本文档中的前台支付文档链接仍指向支付宝对应的官方拉起支付文档；服务端交易类能力则优先指向 open-v3 文档。
:::

## 网页支付

### 例子

```php
Pay::config($this->config);

return Pay::alipay()->web([
    'out_trade_no' => ''.time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 1',
]);
```

#### GET 方式提交

为您考虑到了这一点，如果您想使用 GET 方式提交请求，可以在参数中增加 `['_method' => 'get']` 即可，例如

```php
Pay::config($this->config);

return Pay::alipay()->web([
    'out_trade_no' => ''.time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 1',
    '_method' => 'get',
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open/028r8t?scene=22)，查看「请求参数」一栏。

## H5 支付

### 例子

```php
Pay::config($this->config);

return Pay::alipay()->h5([
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
    'quit_url' => 'https://yansongda.cn',
 ]);
```

#### GET 方式提交

为您考虑到了这一点，如果您想使用 GET 方式提交请求，可以在参数中增加 `['_method' => 'get']` 即可，例如

```php
Pay::config($this->config);

return Pay::alipay()->h5([
    'out_trade_no' => ''.time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 1',
    '_method' => 'get',
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open/02ivbs?scene=21)，查看「请求参数」一栏。

## APP 支付

### 例子

```php
Pay::config($this->config);

// 后续 APP 调用方式不在本文档讨论范围内，请参考官方文档。
return Pay::alipay()->app([
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open/02e7gq?scene=20)，查看「请求参数」一栏。

## 小程序支付

### 例子

```php
Pay::config($this->config);

$result = Pay::alipay()->mini([
    'out_trade_no' => time().'',
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
    'buyer_id' => '2088622190161234',
]);

return $result->get('trade_no');  // 支付宝交易号
// return $result->trade_no;
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open-v3/04n6a3)，查看「请求参数」一栏。

小程序支付接入文档：[https://docs.alipay.com/mini/introduce/pay](https://opendocs.alipay.com/mini/introduce/pay)。

## 刷卡支付（付款码，被扫码）

### 例子

```php
Pay::config($this->config);

$result = Pay::alipay()->pos([
    'out_trade_no' => time(),
    'auth_code' => '284776044441477959',
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open-v3/02s3xk)，查看「请求参数」一栏。

## 扫码支付

:::tip
当前 `scan` 快捷方式默认走 `POST /v3/alipay/trade/precreate`。如需服务端先创建交易再继续后续流程，可直接使用插件 `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Scan\CreatePlugin` 组合到 `mergeCommonPluginsV3()` 中调用。
:::

### 例子

```php
Pay::config($this->config);

$result = Pay::alipay()->scan([
    'out_trade_no' => time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);

return $result->qr_code; // 二维码 url
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`product_code` 等参数。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open-v3/02s4f0)，查看「请求参数」一栏。

## 账户转账

:::tip
在 Alipay V3 中，`transfer` 已按官方 open-v3 REST API 实现，走 `POST /v3/alipay/fund/trans/uni/transfer`。
:::

### 例子

```php
Pay::config($this->config);

$result = Pay::alipay()->transfer([
    'out_biz_no' => '202106051432',
    'trans_amount' => '0.01',
    'product_code' => 'TRANS_ACCOUNT_NO_PWD',
    'biz_scene' => 'DIRECT_TRANSFER',
    'payee_info' => [
        'identity' => 'ghdhjw7124@sandbox.com',
        'identity_type' => 'ALIPAY_LOGON_ID',
        'name' => '沙箱环境'
    ],
]);
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://opendocs.alipay.com/open-v3/02a67f)，查看「请求参数」一栏。


:::tip
转账查询等，请参考 [查询](/docs/v3/alipay/query.md)
:::

## 账单下载地址

:::tip
支付宝官方 V3 SDK 已提供 `GET /v3/alipay/data/dataservice/bill/downloadurl/query`。当前仓库可通过自定义插件组合 `\Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin` 调用。
:::

```php
use Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin;

Pay::config($this->config);

$plugins = Pay::alipay()->mergeCommonPluginsV3([
    QueryPlugin::class,
]);

$result = Pay::alipay()->pay($plugins, [
    'bill_type' => 'trade',
    'bill_date' => '2025-05-01',
]);
```

## 电子回单

:::tip
支付宝官方 V3 SDK 已提供电子回单相关端点：

- `POST /v3/alipay/data/bill/ereceipt/apply`
- `GET /v3/alipay/data/bill/ereceipt/query`

当前仓库可通过自定义插件组合 `\Yansongda\Pay\Plugin\Alipay\V3\Data\Bill\Ereceipt\ApplyPlugin` 与 `\Yansongda\Pay\Plugin\Alipay\V3\Data\Bill\Ereceipt\QueryPlugin` 调用。
:::

```php
use Yansongda\Pay\Plugin\Alipay\V3\Data\Bill\Ereceipt\ApplyPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Data\Bill\Ereceipt\QueryPlugin;

Pay::config($this->config);

$applyPlugins = Pay::alipay()->mergeCommonPluginsV3([
    ApplyPlugin::class,
]);

$applyResult = Pay::alipay()->pay($applyPlugins, [
    'type' => 'TRANSFER',
]);

$queryPlugins = Pay::alipay()->mergeCommonPluginsV3([
    QueryPlugin::class,
]);

$queryResult = Pay::alipay()->pay($queryPlugins, [
    'file_id' => '202603250001',
]);
```

## 尚未迁移到 V3 的转账账户能力

:::tip
`alipay.fund.account.query` 当前未在支付宝官方 V3 SDK 文档中确认到明确等价的 REST 端点，因此仓库中的 `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\QueryAccountPlugin` 仍继续保留为 V2 能力。
:::

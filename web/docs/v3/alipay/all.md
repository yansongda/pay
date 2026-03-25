# 支付宝更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自由的使用任何内置插件和自定义插件调用支付宝的任何 API。

诸如签名、API调用、解密、验签、解包等基础插件已经内置在 Pay 中，
您可以使用 `Pay::alipay()->mergeCommonPluginsV3(array $plugins)` 来获取调用 Alipay V3 API 所必须的常用插件。

:::tip
当前 Alipay V3 已拆分为两类链路：

- 服务端交易类、转账类能力走 open-v3 REST；
- `web` / `h5` / `app` 拉起支付继续走 gateway 兼容模式，但已直接复用 V2 公共插件链，不再单独维护 `GatewayXXXPlugin` 包装类。
:::

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'out_trade_no' => '1514027114',
];

$allPlugins = Pay::alipay()->mergeCommonPluginsV3([QueryPlugin::class]);

$result = Pay::alipay()->pay($allPlugins, $params);
```

如果您需要自定义组合插件，例如在 V3 下调用扫码创建交易或查询账单下载地址，可以这样写：

```php
use Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin as QueryBillUrlPlugin;
use Yansongda\Pay\Plugin\Alipay\V3\Pay\Scan\CreatePlugin;

Pay::config($config);

$scanCreatePlugins = Pay::alipay()->mergeCommonPluginsV3([
    CreatePlugin::class,
]);

$scanCreateResult = Pay::alipay()->pay($scanCreatePlugins, [
    'out_trade_no' => '202603250001',
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - scan create',
]);

$billPlugins = Pay::alipay()->mergeCommonPluginsV3([
    QueryBillUrlPlugin::class,
]);

$billResult = Pay::alipay()->pay($billPlugins, [
    'bill_type' => 'trade',
    'bill_date' => '2025-05-01',
]);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 能力状态矩阵

### 已接入 open-v3 REST

| 能力域 | 当前状态 | 说明 |
|---|---|---|
| 统一收单服务端交易 | 已迁移 | `query` / `refund` / `queryRefund` / `close` / `cancel` 已统一走 V3 REST |
| 扫码创建与扫码支付 | 已迁移 | `scan.create`、`scan.pay` 均已接入 V3 REST |
| 小程序支付 | 已迁移 | `mini.pay` 及其相关服务端交易能力均已走 V3 REST |
| 转账 | 已迁移 | 单笔转账、转账单据查询已走 V3 REST |
| 账单 / 电子回单 | 已迁移 | 对账单下载地址、电子回单申请与查询已走 V3 REST |
| 协议 | 部分迁移 | 查询、解约、执行计划修改已走 V3 REST |
| 预授权 | 部分迁移 | 冻结、解冻、撤销、查询、订单同步已走 V3 REST |
| 会员 | 大部分迁移 | 会员信息、认证初始化/查询、授权令牌/关系查询、人脸、OCR 初始化/服务端检测已接入 V3 REST |

### 继续保留 gateway 兼容模式

| 能力域 | 当前状态 | 说明 |
|---|---|---|
| `web` 支付拉起 | gateway 兼容 | 前台页面拉起，继续复用 V2 公共插件链 |
| `h5` 支付拉起 | gateway 兼容 | 前台页面拉起，继续复用 V2 公共插件链 |
| `app` 支付拉起 | gateway 兼容 | 前台唤起，继续复用 V2 公共插件链 |

### 当前明确保留 V2 的能力

| 能力域 | 当前状态 | 原因 |
|---|---|---|
| `alipay.fund.account.query` | 保留 V2 | 官方 V3 SDK 中暂未确认到明确等价 REST 端点 |
| 协议签约页拉起 | 保留 V2 | 当前仅确认到协议查询、解约、执行计划修改的 V3 REST |
| 协议扣款下单 | 保留 V2 / 复用通用交易 | 暂未确认 Agreement 专属 V3 表达 |
| 身份认证开始认证页拉起 | 保留 V2 | 当前仅确认初始化、查询已存在明确 V3 REST |
| APP 登录授权拉起 | 保留 V2 | 当前仅确认令牌换取、授权关系查询已存在明确 V3 REST |
| OCR `DetectPlugin` | 保留 V2 | 当前仍未确认明确等价的 V3 REST 端点 |

## 详细能力列表

### 支付

### 已迁移到 open-v3 REST

以下能力已经接入 Alipay V3 公共 REST 链路，可直接配合 `mergeCommonPluginsV3()` 使用。

#### 付款码支付

- 统一收单交易支付接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Pos\PayPlugin`

- 统一收单交易查询接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryRefundPlugin`

- 统一收单交易撤销接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\CancelPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin`


#### 扫码支付

- 统一收单线下交易预创建

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Scan\PayPlugin`

- 统一收单交易创建接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Scan\CreatePlugin`

- 统一收单交易查询接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryRefundPlugin`

- 统一收单交易撤销接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\CancelPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin`


#### APP 支付（gateway 兼容）

以下能力中，`PayPlugin` 仍复用 gateway 方式，以兼容前台拉起；查询、退款、关闭等服务端交易能力则复用 V3 REST 交易插件。

- APP 支付接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\App\PayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseInvokeStringPlugin` 插件使用
  :::

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryRefundPlugin`

- 统一收单交易查询接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin`


#### 手机网站支付（gateway 兼容）

- 手机网站支付接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\H5\PayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin` 插件使用
  :::

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryRefundPlugin`

- 统一收单交易查询接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin`

#### 电脑网站支付（gateway 兼容）

- 电脑网站支付接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Web\PayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin` 插件使用
  :::

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin`

- 统一收单交易查询接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryRefundPlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin`

#### JSAPI支付

`mini` 支付已切换至 open-v3 REST，查询、退款、关闭、撤销等也统一复用 V3 REST 交易插件。

- 统一收单交易创建接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Mini\PayPlugin`

- 统一收单交易撤销接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\CancelPlugin`

- 统一收单交易查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryRefundPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin`

### 其他能力（当前仍主要复用 V2 插件）

以下能力尚未纳入本轮 Alipay V3 REST 迁移范围，当前文档继续展示已有 V2 插件能力。

### 刷脸支付

- 刷脸支付初始化

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Face\InitPlugin`

- 查询刷脸结果信息接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Face\QueryPlugin`

### 商家扣款

#### 签约

- 支付宝个人协议页面签约接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Sign\SignPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin` 插件并传参 `['_method' => 'GET']` 使用，以便直接返回协议字符串
  :::

- 支付宝个人代扣协议查询接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Agreement\Sign\QueryPlugin`

- 支付宝个人代扣协议解约接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Agreement\Sign\UnsignPlugin`

- 周期性扣款协议执行计划修改接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Agreement\Sign\ModifyPlugin`

  :::tip
  协议查询、解约、执行计划修改已可走 V3 REST；协议签约页拉起与协议扣款下单仍暂保留 V2 能力。
  :::

#### 支付

- app支付接口2.0

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\AppPayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseInvokeStringPlugin` 插件使用
  :::

- 统一收单交易支付接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Pos\PayPlugin`

- 统一收单交易查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin`

- 统一收单交易撤销接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\CancelPlugin`

  :::tip
  协议扣款交易类能力当前直接复用通用交易 V3 插件；仅 `AppPayPlugin` 这类前台拉起能力仍保留 V2 表达。
  :::

#### 账单

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin`

### 预授权支付

#### 预授权

- 线上资金授权冻结接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Auth\AppFreezePlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseInvokeStringPlugin` 插件使用
  :::

- 资金授权操作查询接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Auth\QueryPlugin`

- 资金授权撤销接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Auth\CancelPlugin`

- 资金授权解冻接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Auth\UnfreezePlugin`

- 资金授权发码接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Auth\ScanFreezePlugin`

- 资金授权冻结接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Auth\PosFreezePlugin`

#### 交易

- 统一收单交易支付接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Pos\PayPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\ClosePlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryRefundPlugin`

- 统一收单交易查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\QueryPlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\RefundPlugin`

- 支付宝订单信息同步接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Pay\Authorization\Pay\SyncPlugin`

  :::tip
  预授权冻结、解冻、撤销、查询、发码与订单信息同步已可走 V3 REST；预授权转支付、退款、关闭等交易类能力当前直接复用通用交易 V3 插件。
  :::

#### 账单

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin`

## 营销

### 红包

- 资金转账页面支付接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Marketing\Redpack\WebPayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin` 插件使用
  :::

- 现金红包无线支付接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Marketing\Redpack\AppPayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseInvokeStringPlugin` 插件使用
  :::

## 资金

### 商家分账

#### 分账关系维护

- 分账关系绑定

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Relation\BindPlugin `

- 分账关系解绑

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Relation\UnbindPlugin`

- 分账关系查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Relation\QueryPlugin`

#### 分账请求

- 统一收单交易结算接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Request\SettleOrderPlugin`

#### 分账查询

- 分账比例查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Query\RatePlugin`

- 分账剩余金额查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Query\OnsettlePlugin`

- 交易分账查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Royalty\Query\SettlePlugin`

### 花呗分期

- APP 支付接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\PCreditPayInstallment\AppPayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseInvokeStringPlugin` 插件使用
  :::

- H5 手机网站支付

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\PCreditPayInstallment\H5PayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin` 插件使用
  :::

- 刷卡支付（付款码，被扫码）

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\PCreditPayInstallment\PosPayPlugin`

- 扫码支付

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\PCreditPayInstallment\ScanPayPlugin`

### 转账到支付宝账户

- 支付宝资金账户资产查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\QueryAccountPlugin`

  :::tip
  当前未在支付宝官方 V3 SDK 中确认到与 `alipay.fund.account.query` 明确等价的 REST 端点，故暂时继续保留 V2 插件实现。
  :::

- 申请电子回单(incubating)

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Bill\Ereceipt\ApplyPlugin`

- 查询电子回单状态(incubating)

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Bill\Ereceipt\QueryPlugin`

#### 资金

- 转账业务单据查询接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Fund\Transfer\Fund\QueryPlugin`

- 单笔转账接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Fund\Transfer\Fund\TransferPlugin`

#### 账单

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V3\Data\Dataservice\Bill\DownloadUrl\QueryPlugin`

## 会员

### 支付宝身份验证

- 身份认证记录查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\Certification\QueryPlugin`

- 身份认证初始化服务

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\Certification\InitPlugin`

- 身份认证开始认证

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Certification\CertifyPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin` 插件并传参 `['_method' => 'GET']` 使用
  :::

  :::tip
  身份认证初始化与查询已可走 V3 REST；开始认证页拉起当前仍保留 V2 表达。
  :::

### APP支付宝登录

- 用户登录授权

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization\AuthPlugin`

- 换取授权访问令牌

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\Authorization\TokenPlugin`

- 支付宝会员授权信息查询接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\Authorization\QueryPlugin`

  :::tip
  换取令牌与授权关系查询已可走 V3 REST；用户登录授权拉起当前仍保留 V2 表达。
  :::

### 获取会员信息

- 支付宝会员授权信息查询接口

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\DetailPlugin`

- 换取授权访问令牌

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\Authorization\TokenPlugin`

- 用户授权关系查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\Authorization\QueryPlugin`

### 人脸认证

#### 活体检测

- 活体检测初始化

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\FaceCheck\AppInitPlugin`

- 活体检测结果查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\FaceCheck\AppQueryPlugin`

#### 人脸核身

- APP 人脸核身初始化

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification\AppInitPlugin`

- APP 人脸核身结果查询

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification\AppQueryPlugin`

- H5人脸核身初始化

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification\H5InitPlugin`

- H5人脸核身开始认证

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification\H5VerifyPlugin`

- H5人脸核身查询记录

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification\H5QueryPlugin`

- 纯服务端人脸核身

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\FaceVerification\ServerVerifyPlugin`

### OCR

- 服务端OCR

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\Ocr\ServerDetectPlugin`

- App端OCR初始化

  `\Yansongda\Pay\Plugin\Alipay\V3\Member\Ocr\AppInitPlugin`

- 文字识别OCR

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Ocr\DetectPlugin`

  :::tip
  服务端 OCR 与 App 端 OCR 初始化已可走 V3 REST；`DetectPlugin` 当前仍未确认有明确等价的 V3 REST 端点，故继续保留 V2。
  :::

### 文件上传

- 商品文件上传

  `\Yansongda\Pay\Plugin\Alipay\V2\Merchant\Item\FileUploadPlugin`

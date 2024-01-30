# 支付宝更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自有的使用任何内置插件和自定义插件调用支付宝的任何 API。

诸如签名、API调用、解密、验签、解包等基础插件已经内置在 Pay 中，
您可以使用 `Pay::alipay()->mergeCommonPlugins(array $plugins)` 来获取调用 API 所必须的常用插件

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'out_trade_no' => '1514027114',
];

$allPlugins = Pay::alipay()->mergeCommonPlugins([QueryPlugin::class]);

$result = Pay::alipay()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [yansongda/artful](https://artful.yansongda.cn/)

## 支付

### 付款码支付

- 统一收单交易支付接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\PayPlugin`

- 统一收单交易查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\QueryPlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\RefundPlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\QueryRefundPlugin`

- 统一收单交易撤销接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\CancelPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\ClosePlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Pos\QueryBillUrlPlugin`


### 扫码支付

- 统一收单线下交易预创建

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\PayPlugin`

- 统一收单交易创建接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\CreatePlugin`

- 统一收单交易查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\QueryPlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\RefundPlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\QueryRefundPlugin`

- 统一收单交易撤销接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\CancelPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\ClosePlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Scan\QueryBillUrlPlugin`


### APP 支付

- APP 支付接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\App\PayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseInvokeStringPlugin` 插件使用
  :::

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\App\RefundPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\App\ClosePlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\App\QueryRefundPlugin`

- 统一收单交易查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\App\QueryPlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\App\QueryBillUrlPlugin`


### 手机网站支付

- 手机网站支付接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\PayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin` 插件使用
  :::

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\RefundPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\ClosePlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\QueryRefundPlugin`

- 统一收单交易查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\QueryPlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\H5\QueryBillUrlPlugin`

### 电脑网站支付

- 电脑网站支付接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\PayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin` 插件使用
  :::

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\ClosePlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\RefundPlugin`

- 统一收单交易查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\QueryPlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\QueryRefundPlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Web\QueryBillUrlPlugin`

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
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin` 插件并传参 `['_method' => 'GET']` 使用
  :::

- 支付宝个人代扣协议查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Sign\QueryPlugin`

- 支付宝个人代扣协议解约接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Sign\UnsignPlugin`

- 周期性扣款协议执行计划修改接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Sign\ModifyPlugin`

#### 支付

- app支付接口2.0

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\AppPayPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseInvokeStringPlugin` 插件使用
  :::

- 统一收单交易支付接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\PayPlugin`

- 统一收单交易查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\QueryPlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\RefundPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\ClosePlugin`

- 统一收单交易撤销接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Pay\CancelPlugin`

#### 账单

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Agreement\Bill\QueryUrlPlugin`

### 预授权支付

#### 预授权

- 线上资金授权冻结接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth\AppFreezePlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseInvokeStringPlugin` 插件使用
  :::

- 资金授权操作查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth\QueryPlugin`

- 资金授权撤销接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth\CancelPlugin`

- 资金授权解冻接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth\UnfreezePlugin`

- 资金授权发码接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth\ScanFreezePlugin`

- 资金授权冻结接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Auth\PosFreezePlugin`

#### 交易

- 统一收单交易支付接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\PayPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\ClosePlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\QueryRefundPlugin`

- 统一收单交易查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\QueryPlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\RefundPlugin`

- 支付宝订单信息同步接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Pay\SyncPlugin`

#### 账单

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Authorization\Bill\QueryUrlPlugin`

### JSAPI支付

- 统一收单交易创建接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\PayPlugin`

- 统一收单交易撤销接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\CancelPlugin`

- 统一收单交易查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\QueryPlugin`

- 统一收单交易退款接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\RefundPlugin`

- 统一收单交易退款查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\QueryRefundPlugin`

- 统一收单交易关闭接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\ClosePlugin`

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V2\Pay\Mini\QueryBillUrlPlugin`


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

- 申请电子回单(incubating)

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\ApplyReceiptPlugin`

- 查询电子回单状态(incubating)

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\QueryReceiptPlugin`

#### 资金

- 转账业务单据查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Fund\QueryPlugin`

- 单笔转账接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Fund\TransferPlugin`

#### 账单

- 查询对账单下载地址

  `\Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Bill\QueryUrlPlugin`

## 会员

### 支付宝身份验证

- 身份认证记录查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Certification\QueryPlugin`

- 身份认证初始化服务

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Certification\InitPlugin`

- 身份认证开始认证

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Certification\CertifyPlugin`

  :::warning 注意
  通常搭配 `\Yansongda\Pay\Plugin\Alipay\V2\ResponseHtmlPlugin` 插件并传参 `['_method' => 'GET']` 使用
  :::

### APP支付宝登录

- 用户登录授权

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization\AuthPlugin`

- 换取授权访问令牌

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization\TokenPlugin`

- 支付宝会员授权信息查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization\QueryPlugin`

### 获取会员信息

- 支付宝会员授权信息查询接口

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\DetailPlugin`

- 换取授权访问令牌

  ``\Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization\TokenPlugin``

- 用户授权关系查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Authorization\QueryPlugin`

### 人脸认证

#### 活体检测

- 活体检测初始化

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\FaceCheck\AppInitPlugin`

- 活体检测结果查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\FaceCheck\AppQueryPlugin`

#### 人脸核身

- APP 人脸核身初始化

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification\AppInitPlugin`

- APP 人脸核身结果查询

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification\AppQueryPlugin`

- H5人脸核身初始化

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification\H5InitPlugin`

- H5人脸核身开始认证

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification\H5VerifyPlugin`

- H5人脸核身查询记录

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification\H5QueryPlugin`

- 纯服务端人脸核身

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\FaceVerification\ServerVerifyPlugin`

#### OCR

- 服务端OCR

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Ocr\ServerDetectPlugin`

- App端OCR初始化

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Ocr\AppInitPlugin`

- 文字识别OCR

  `\Yansongda\Pay\Plugin\Alipay\V2\Member\Ocr\DetectPlugin`

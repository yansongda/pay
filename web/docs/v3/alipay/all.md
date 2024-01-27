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

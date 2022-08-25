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

关于插件的详细介绍，如果您感兴趣，可以参考 [这篇说明文档](/docs/v3/kernel/plugin.md)

## 账务API插件

### 下载对账单

- `Yansongda\Pay\Plugin\Alipay\Data\BillDownloadUrlQueryPlugin`

### 申请电子回单

- `Yansongda\Pay\Plugin\Alipay\Data\BillEreceiptApplyPlugin`

### 查询电子回单状态

- `Yansongda\Pay\Plugin\Alipay\Data\BillEreceiptQueryPlugin`

## 生活缴费API插件

### 缴费直连代扣订单支付状态查询

- `Yansongda\Pay\Plugin\Alipay\Ebpp\PdeductBillStatusPlugin`

### 公共事业缴费直连代扣扣款支付接口

- `Yansongda\Pay\Plugin\Alipay\Ebpp\PdeductPayPlugin`

### 缴费直连代扣签约

- `Yansongda\Pay\Plugin\Alipay\Ebpp\PdeductSignAddPlugin`

### 缴费直连代扣取消签约

- `Yansongda\Pay\Plugin\Alipay\Ebpp\PdeductSignCancelPlugin`

## 资金API插件

### 支付宝资金账户资产查询接口

- `Yansongda\Pay\Plugin\Alipay\Fund\AccountQueryPlugin`

### 资金授权冻结接口

- `Yansongda\Pay\Plugin\Alipay\Fund\AuthOrderFreezePlugin`

### 资金授权解冻接口

- `Yansongda\Pay\Plugin\Alipay\Fund\AuthOrderUnfreezePlugin`

### 查询转账订单接口

- `Yansongda\Pay\Plugin\Alipay\Fund\TransOrderQueryPlugin`

### 转账业务单据查询接口

- `Yansongda\Pay\Plugin\Alipay\Fund\TransCommonQueryPlugin`

### 资金转账页面支付接口

- `Yansongda\Pay\Plugin\Alipay\Fund\TransPagePayPlugin`

:::warning
该插件需配合 `HtmlResponsePlugin` 插件一起使用
:::

### 单笔转账接口

- `Yansongda\Pay\Plugin\Alipay\Fund\TransUniTransferPlugin`

### 单笔转账到银行账户接口

- `Yansongda\Pay\Plugin\Alipay\Fund\TransTobankTransferPlugin`

## 工具类API

### 换取授权访问令牌

- `Yansongda\Pay\Plugin\Alipay\Tools\SystemOauthTokenPlugin`

### 换取应用授权令牌

- `Yansongda\Pay\Plugin\Alipay\Tools\OpenAuthTokenAppPlugin`

### 查询某个应用授权AppAuthToken的授权信息

- `Yansongda\Pay\Plugin\Alipay\Tools\OpenAuthTokenAppQueryPlugin`

## 会员API

### 支付宝会员授权信息查询接口

- `Yansongda\Pay\Plugin\Alipay\User\InfoSharePlugin`

### 支付宝个人协议页面签约接口

- `Yansongda\Pay\Plugin\Alipay\User\AgreementPageSignPlugin`

:::warning
该插件需配合 `HtmlResponsePlugin` 插件一起使用
:::

### 支付宝个人代扣协议查询接口

- `Yansongda\Pay\Plugin\Alipay\User\AgreementQueryPlugin`

### 支付宝个人代扣协议解约接口

- `Yansongda\Pay\Plugin\Alipay\User\AgreementUnsignPlugin`

### 周期性扣款协议执行计划修改接口

- `Yansongda\Pay\Plugin\Alipay\User\AgreementExecutionPlanModifyPlugin`

### 协议由普通通用代扣协议产品转移到周期扣协议产品

- `Yansongda\Pay\Plugin\Alipay\User\AgreementTransferPlugin`

# 微信更多方便的插件

得益于 yansongda/pay 的基础架构和良好的插件机制，
您可以自有的使用任何内置插件和自定义插件调用支付宝的任何 API。

诸如签名、API调用、解密、验签、解包等基础插件已经内置在 Pay 中，
您可以使用 `Pay::wechat()->mergeCommonPlugins(array $plugins)` 来获取调用 API 所必须的常用插件

首先，查找你想使用的插件，然后

```php
Pay::config($config);

$params = [
    'transaction_id' => '1217752501201407033233368018',
];

$allPlugins = Pay::wechat()->mergeCommonPlugins([QueryPlugin::class]);

$result = Pay::wechat()->pay($allPlugins, $params);
```

关于插件的详细介绍，如果您感兴趣，可以参考 [这篇说明文档](/docs/v3/kernel/plugin.md)

## 基础支付-合单支付

### 合单APP下单

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\AppPrepayPlugin`

### 合单H5下单

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\H5PrepayPlugin`

### 合单JSAPI下单

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\JsapiPrepayPlugin`

### 合单小程序下单

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\MiniPrepayPlugin`

### 合单Native下单

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\NativePrepayPlugin`

### APP调起支付

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\InvokeAppPrepayPlugin`

### JSAPI调起支付

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\InvokeJsapiPrepayPlugin`

### 小程序调起支付

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\InvokeMiniPrepayPlugin`

### 申请退款

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\RefundPlugin`

### 查询退款

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\FindRefundPlugin`

### 申请交易账单

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\GetTradeBillPlugin`

### 申请资金账单

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\GetFlowBillPlugin`

### 下载账单

- `Yansongda\Pay\Plugin\Wechat\Pay\Combine\DownloadBillPlugin`

## 资金应用-分账

### 添加分账接收方

- `Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\AddReceiverPlugin`

### 请求分账

- `Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\CreatePlugin`

### 删除分账接收方

- `Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\DeleteReceiverPlugin`

### 查询剩余待分金额

- `Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\QueryAmountsPlugin`

### 查询分账结果

- `Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\QueryPlugin`

### 查询分账回退结果

- `Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\QueryReturnPlugin`

### 请求分账回退

- `Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\ReturnPlugin`

### 解冻剩余资金

- `Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\UnfreezePlugin`

### 查询最大分账比例

- `Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\QueryMerchantConfigsPlugin`

### 下载账单

- `Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing\DownloadBillPlugin`

## 资金应用-转账到零钱

### 发起批量转账

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\CreatePlugin`

### 微信批次单号查询批次单

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\QueryBatchIdPlugin`

### 微信明细单号查询明细单

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\QueryBatchDetailIdPlugin`

### 商家批次单号查询批次单

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\QueryOutBatchNoPlugin`

### 商家明细单号查询明细单

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\QueryOutBatchDetailNoPlugin`

### 转账电子回单申请受理

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\CreateBillReceiptPlugin`

### 查询转账电子回单

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\QueryBillReceiptPlugin`

### 转账明细电子回单受理

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\CreateDetailReceiptPlugin`

### 查询转账明细电子回单受理结果

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\QueryBillReceiptPlugin`

### 下载电子回单

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\DownloadReceiptPlugin`

### 查询转账明细电子回单受理结果

- `\Yansongda\Pay\Plugin\Wechat\Fund\Transfer\QueryDetailReceiptPlugin`

### 查询账户实时余额

- `\Yansongda\Pay\Plugin\Wechat\Fund\Balance\QueryPlugin`

### 查询账户日终余额

- `\Yansongda\Pay\Plugin\Wechat\Fund\Balance\QueryDayEndPlugin`

## 营销工具-代金券

### 创建代金券批次

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\CreatePlugin`

### 暂停代金券批次

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\PausePlugin`

### 根据商户号查用户的券

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\QueryCouponDetailPlugin`

### 查询批次详情

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\QueryStockDetailPlugin`

### 查询代金券可用单品

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\QueryStockItemsPlugin`

### 查询代金券可用商户

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\QueryStockMerchantsPlugin`

### 下载批次退款明细

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\QueryStockRefundFlowPlugin`

### 条件查询批次列表

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\QueryStocksPlugin`

### 下载批次核销明细

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\QueryStockUseFlowPlugin`

### 根据商户号查用户的券

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\QueryUserCouponsPlugin`

### 重启代金券批次

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\RestartPlugin`

### 发放代金券批次

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\SendPlugin`

### 设置消息通知地址API

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\SetCallbackPlugin`

### 激活代金券批次

- `Yansongda\Pay\Plugin\Wechat\Marketing\Coupon\StartPlugin`

## 风险合规-消费者投诉

### 查询投诉单列表

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\QueryComplaintsPlugin`

### 查询投诉单详情

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\QueryComplaintDetailPlugin`

### 查询投诉协商历史

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\QueryComplaintNegotiationPlugin`

### 创建投诉通知回调地址

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\SetCallbackPlugin`

### 查询投诉通知回调地址

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\QueryCallbackPlugin`

### 更新投诉通知回调地址

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\UpdateCallbackPlugin`

### 删除投诉通知回调地址

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\DeleteCallbackPlugin`

### 回复用户

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\ResponseComplaintPlugin`

### 反馈处理完成

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\CompleteComplaintPlugin`

### 更新退款进度

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\UpdateRefundPlugin`

### 图片下载

- `Yansongda\Pay\Plugin\Wechat\Risk\Complaints\DownloadMediaPlugin`


## 服务商-行业方案

### 电商收付通（退款）

#### 申请退款

- `Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund\ApplyPlugin`

#### 查询退款

- `Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund\FindPlugin`

#### 查询垫付回补结果

- `Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund\FindReturnAdvancePlugin`

#### 垫付退款回补

- `Yansongda\Pay\Plugin\Wechat\Ecommerce\Refund\ReturnAdvancePlugin`

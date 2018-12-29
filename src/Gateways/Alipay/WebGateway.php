<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Events;

class WebGateway implements GatewayInterface
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     *
     * @return Response
     */
    public function pay($endpoint, array $payload): Response
    {
        $payload['method'] = $this->getMethod();
        $payload['biz_content'] = json_encode(array_merge(
            json_decode($payload['biz_content'], true),
            ['product_code' => $this->getProductCode()]
        ));
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(Events::PAY_STARTED, new Events\PayStarted('Alipay', 'Web/Wap', $endpoint, $payload));

        return $this->buildPayHtml($endpoint, $payload);
    }

    /**
     * Build Html response.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @return Response
     */
    protected function buildPayHtml($endpoint, $payload): Response
    {
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$endpoint."' method='POST'>";
        foreach ($payload as $key => $val) {
            $val = str_replace("'", '&apos;', $val);
            $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
        $sHtml .= "<script>document.forms['alipaysubmit'].submit();</script>";

        return Response::create($sHtml);
    }

    /**
     * Get method config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return 'alipay.trade.page.pay';
    }

    /**
     * Get productCode config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getProductCode(): string
    {
        return 'FAST_INSTANT_TRADE_PAY';
    }
}

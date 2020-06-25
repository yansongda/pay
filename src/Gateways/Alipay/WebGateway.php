<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Events;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Gateways\Alipay;

class WebGateway extends Gateway
{
    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     *
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     */
    public function pay($endpoint, array $payload): Response
    {
        $biz_array = json_decode($payload['biz_content'], true);
        $biz_array['product_code'] = $this->getProductCode();

        $method = $biz_array['http_method'] ?? 'POST';

        unset($biz_array['http_method']);
        if ((Alipay::MODE_SERVICE === $this->mode) && (!empty(Support::getInstance()->pid))) {
            $biz_array['extend_params'] = is_array($biz_array['extend_params']) ? array_merge(['sys_service_provider_id' => Support::getInstance()->pid], $biz_array['extend_params']) : ['sys_service_provider_id' => Support::getInstance()->pid];
        }
        $payload['method'] = $this->getMethod();
        $payload['biz_content'] = json_encode($biz_array);
        $payload['sign'] = Support::generateSign($payload);

        Events::dispatch(new Events\PayStarted('Alipay', 'Web/Wap', $endpoint, $payload));

        return $this->buildPayHtml($endpoint, $payload, $method);
    }

    /**
     * Find.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param $order
     */
    public function find($order): array
    {
        return [
            'method' => 'alipay.trade.query',
            'biz_content' => json_encode(is_array($order) ? $order : ['out_trade_no' => $order]),
        ];
    }

    /**
     * Build Html response.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     * @param array  $payload
     * @param string $method
     */
    protected function buildPayHtml($endpoint, $payload, $method = 'POST'): Response
    {
        if ('GET' === strtoupper($method)) {
            return new RedirectResponse($endpoint.'&'.http_build_query($payload));
        }

        $sHtml = "<form id='alipay_submit' name='alipay_submit' action='".$endpoint."' method='".$method."'>";
        foreach ($payload as $key => $val) {
            $val = str_replace("'", '&apos;', $val);
            $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
        $sHtml .= "<script>document.forms['alipay_submit'].submit();</script>";

        return new Response($sHtml);
    }

    /**
     * Get method config.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function getMethod(): string
    {
        return 'alipay.trade.page.pay';
    }

    /**
     * Get productCode config.
     *
     * @author yansongda <me@yansongda.cn>
     */
    protected function getProductCode(): string
    {
        return 'FAST_INSTANT_TRADE_PAY';
    }
}

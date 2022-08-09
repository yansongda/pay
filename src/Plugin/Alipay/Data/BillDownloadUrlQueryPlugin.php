<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Data;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

/**
 * @see https://opendocs.alipay.com/open/02fkbl
 */
class BillDownloadUrlQueryPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.data.dataservice.bill.downloadurl.query';
    }
}

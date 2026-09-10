<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class BestpayConfig extends AbstractConfig
{
    private string $merchantNo = '';
    private string $institutionCode = '';
    private string $institutionType = 'MERCHANT';
    private string $mchSecretCertPath = '';
    private string $mchSecretCertPassword = '';
    private string $bestpayPublicCertPath = '';
    private string $apiVersion = '1.0.3';
    private ?string $notifyUrl = null;
    private ?string $returnUrl = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setMerchantNo(string $value): void
    {
        $this->merchantNo = $value;
    }

    public function setInstitutionCode(string $value): void
    {
        $this->institutionCode = $value;
    }

    public function setInstitutionType(string $value): void
    {
        $this->institutionType = $value;
    }

    public function setMchSecretCertPath(string $value): void
    {
        $this->mchSecretCertPath = $value;
    }

    public function setMchSecretCertPassword(string $value): void
    {
        $this->mchSecretCertPassword = $value;
    }

    public function setBestpayPublicCertPath(string $value): void
    {
        $this->bestpayPublicCertPath = $value;
    }

    public function setApiVersion(string $value): void
    {
        $this->apiVersion = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setReturnUrl(?string $value): void
    {
        $this->returnUrl = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getMerchantNo(): string
    {
        return $this->merchantNo;
    }

    public function getInstitutionCode(): string
    {
        return $this->institutionCode;
    }

    public function getInstitutionType(): string
    {
        return $this->institutionType;
    }

    public function getMchSecretCertPath(): string
    {
        return $this->mchSecretCertPath;
    }

    public function getMchSecretCertPassword(): string
    {
        return $this->mchSecretCertPassword;
    }

    public function getBestpayPublicCertPath(): string
    {
        return $this->bestpayPublicCertPath;
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @throws InvalidConfigException
     */
    protected function supportedModes(): array
    {
        // 翼支付一期无服务商模式
        return [Pay::MODE_NORMAL, Pay::MODE_SANDBOX];
    }

    /**
     * @throws InvalidConfigException
     */
    protected function validateRequired(): void
    {
        $this->validateNotEmpty(
            ['merchantNo', 'institutionCode', 'mchSecretCertPath', 'mchSecretCertPassword', 'bestpayPublicCertPath'],
            Exception::CONFIG_BESTPAY_INVALID,
            '配置异常: 缺少翼支付配置'
        );
    }
}

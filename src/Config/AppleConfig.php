<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class AppleConfig extends AbstractConfig
{
    /** 未配置 apple_root_ca 时的内置信任锚（可配置覆盖） */
    private const DEFAULT_ROOT_CA = __DIR__.'/../Certificate/AppleRootCA-G3.pem';

    /** 未配置 apple_intermediate_ca 时的内置中间证书（仅作 PKCS#7 报文缺少 intermediate 时的兜底） */
    private const DEFAULT_INTERMEDIATE_CA = __DIR__.'/../Certificate/AppleAAICAG3.pem';

    private string $merchantId = '';
    private string $paymentProcessingCert = '';
    private ?string $paymentProcessingCertPassphrase = null;
    private ?string $appleRootCa = null;
    private ?string $appleIntermediateCa = null;
    private ?string $issuerId = null;
    private ?string $bundleId = null;
    private ?string $apiKeyId = null;
    private ?string $apiPrivateKey = null;
    private ?string $notifyUrl = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setMerchantId(string $value): void
    {
        $this->merchantId = $value;
    }

    public function setPaymentProcessingCert(string $value): void
    {
        $this->paymentProcessingCert = $value;
    }

    public function setPaymentProcessingCertPassphrase(?string $value): void
    {
        $this->paymentProcessingCertPassphrase = $value;
    }

    public function setAppleRootCa(?string $value): void
    {
        $this->appleRootCa = $value;
    }

    public function setAppleIntermediateCa(?string $value): void
    {
        $this->appleIntermediateCa = $value;
    }

    public function setIssuerId(?string $value): void
    {
        $this->issuerId = $value;
    }

    public function setBundleId(?string $value): void
    {
        $this->bundleId = $value;
    }

    public function setApiKeyId(?string $value): void
    {
        $this->apiKeyId = $value;
    }

    public function setApiPrivateKey(?string $value): void
    {
        $this->apiPrivateKey = $value;
    }

    public function setNotifyUrl(?string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function getPaymentProcessingCert(): string
    {
        return $this->paymentProcessingCert;
    }

    public function getPaymentProcessingCertPassphrase(): ?string
    {
        return $this->paymentProcessingCertPassphrase;
    }

    public function getAppleRootCa(): string
    {
        return $this->appleRootCa ?? self::DEFAULT_ROOT_CA;
    }

    public function getAppleIntermediateCa(): string
    {
        return $this->appleIntermediateCa ?? self::DEFAULT_INTERMEDIATE_CA;
    }

    public function getIssuerId(): ?string
    {
        return $this->issuerId;
    }

    public function getBundleId(): ?string
    {
        return $this->bundleId;
    }

    public function getApiKeyId(): ?string
    {
        return $this->apiKeyId;
    }

    public function getApiPrivateKey(): ?string
    {
        return $this->apiPrivateKey;
    }

    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @throws InvalidConfigException 缺少必要配置参数或 App Store Server API 配置不完整
     */
    protected function validateRequired(): void
    {
        $this->validateNotEmpty(
            ['merchantId', 'paymentProcessingCert'],
            Exception::CONFIG_APPLE_INVALID,
            '配置异常: 缺少 Apple 配置'
        );

        $apiFields = ['issuerId', 'bundleId', 'apiKeyId', 'apiPrivateKey'];
        $configuredCount = 0;

        foreach ($apiFields as $field) {
            if (!empty($this->{$field})) {
                ++$configuredCount;
            }
        }

        if (0 < $configuredCount && count($apiFields) > $configuredCount) {
            throw new InvalidConfigException(
                Exception::CONFIG_APPLE_INVALID,
                '配置异常: Apple App Store Server API 配置不完整 -- [issuer_id]/[bundle_id]/[api_key_id]/[api_private_key] 需同时配置'
            );
        }
    }
}

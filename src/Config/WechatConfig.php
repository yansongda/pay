<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Wechat;

class WechatConfig extends AbstractConfig
{
    private string $mchId = '';
    private string $mchSecretKey = '';
    private string $mchSecretCert = '';
    private string $mchPublicCertPath = '';
    private string $notifyUrl = '';
    private ?string $mchSecretKeyV2 = null;
    private ?string $mpAppId = null;
    private ?string $miniAppId = null;
    private ?string $appId = null;
    private ?string $subMchId = null;
    private ?string $subMpAppId = null;
    private ?string $subMiniAppId = null;
    private ?string $subAppId = null;
    private int $mode = Pay::MODE_NORMAL;
    private WechatConfigVirtualPay $virtualPay;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values, string $tenant = 'default')
    {
        $this->virtualPay = new WechatConfigVirtualPay();
        parent::__construct($values, $tenant);
    }

    public function setMchId(string $value): void
    {
        $this->mchId = $value;
    }

    public function setMchSecretKey(string $value): void
    {
        $this->mchSecretKey = $value;
    }

    public function setMchSecretCert(string $value): void
    {
        $this->mchSecretCert = $value;
    }

    public function setMchPublicCertPath(string $value): void
    {
        $this->mchPublicCertPath = $value;
    }

    public function setNotifyUrl(string $value): void
    {
        $this->notifyUrl = $value;
    }

    public function setMchSecretKeyV2(?string $value): void
    {
        $this->mchSecretKeyV2 = $value;
    }

    public function setMpAppId(?string $value): void
    {
        $this->mpAppId = $value;
    }

    public function setMiniAppId(?string $value): void
    {
        $this->miniAppId = $value;
    }

    public function setAppId(?string $value): void
    {
        $this->appId = $value;
    }

    public function setSubMchId(?string $value): void
    {
        $this->subMchId = $value;
    }

    public function setSubMpAppId(?string $value): void
    {
        $this->subMpAppId = $value;
    }

    public function setSubMiniAppId(?string $value): void
    {
        $this->subMiniAppId = $value;
    }

    public function setSubAppId(?string $value): void
    {
        $this->subAppId = $value;
    }

    /**
     * @param array<string, string> $value
     */
    public function setWechatPublicCertPath(array $value): void
    {
        foreach ($value as $serialNo => $cert) {
            CertManager::wechatSetCertBySerial($this->tenant, $serialNo, $cert);
        }
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getMchId(): string
    {
        return $this->mchId;
    }

    public function getMchSecretKey(): string
    {
        return $this->mchSecretKey;
    }

    public function getMchSecretCert(): string
    {
        return $this->mchSecretCert;
    }

    public function getMchPublicCertPath(): string
    {
        return $this->mchPublicCertPath;
    }

    public function getNotifyUrl(): string
    {
        return $this->notifyUrl;
    }

    public function getMchSecretKeyV2(): ?string
    {
        return $this->mchSecretKeyV2;
    }

    public function getMpAppId(): ?string
    {
        return $this->mpAppId;
    }

    public function getMiniAppId(): ?string
    {
        return $this->miniAppId;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function getSubMchId(): ?string
    {
        return $this->subMchId;
    }

    public function getSubMpAppId(): ?string
    {
        return $this->subMpAppId;
    }

    public function getSubMiniAppId(): ?string
    {
        return $this->subMiniAppId;
    }

    public function getSubAppId(): ?string
    {
        return $this->subAppId;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @param array<string, mixed>|WechatConfigVirtualPay $value
     */
    public function setVirtualPay(array|WechatConfigVirtualPay $value): void
    {
        $this->virtualPay = is_array($value)
            ? (new WechatConfigVirtualPay())->unserializeArray($value)
            : $value;
    }

    public function getVirtualPay(): WechatConfigVirtualPay
    {
        return $this->virtualPay;
    }

    /**
     * 根据微信支付类型获取对应的 AppId.
     *
     * @param string $type 支付类型: 'mp'(公众号), 'mini'(小程序), 'app'(APP)
     */
    public function getAppIdByType(string $type = 'mp'): ?string
    {
        return match ($type) {
            'mini' => $this->miniAppId,
            'app' => $this->appId,
            default => $this->mpAppId,
        };
    }

    /**
     * 根据微信支付类型获取对应的子商户 AppId（服务商模式）.
     *
     * @param string $type 支付类型: 'mp'(公众号), 'mini'(小程序), 'app'(APP)
     */
    public function getSubAppIdByType(string $type = 'mp'): ?string
    {
        return match ($type) {
            'mini' => $this->subMiniAppId,
            'app' => $this->subAppId,
            default => $this->subMpAppId,
        };
    }

    /**
     * @throws InvalidConfigException 缺少必要配置参数
     */
    protected function validateRequired(): void
    {
        $this->validateNotEmpty(
            ['mchId', 'mchSecretKey', 'mchSecretCert', 'mchPublicCertPath'],
            Exception::CONFIG_WECHAT_INVALID,
            '配置异常: 缺少微信配置'
        );

        if (Wechat::MCH_SECRET_KEY_LENGTH_BYTE !== strlen($this->mchSecretKey)) {
            throw new InvalidConfigException(
                Exception::CONFIG_WECHAT_INVALID,
                '配置异常: mch_secret_key 长度应为 32 字节'
            );
        }

        if (Pay::MODE_SERVICE === $this->mode && empty($this->subMchId)) {
            throw new InvalidConfigException(
                Exception::CONFIG_WECHAT_INVALID,
                '配置异常: 服务商模式下缺少 [sub_mch_id]'
            );
        }
    }
}

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
    private string $mch_id = '';
    private string $mch_secret_key = '';
    private string $mch_secret_cert = '';
    private string $mch_public_cert_path = '';
    private string $notify_url = '';
    private ?string $mch_secret_key_v2 = null;
    private ?string $mp_app_id = null;
    private ?string $mini_app_id = null;
    private ?string $app_id = null;
    private ?string $sub_mch_id = null;
    private ?string $sub_mp_app_id = null;
    private ?string $sub_mini_app_id = null;
    private ?string $sub_app_id = null;
    private array $wechat_public_cert_path = [];
    private ?string $mini_app_key_virtual_pay = null;
    private int $mode = Pay::MODE_NORMAL;

    public function setMchId(string $value): void
    {
        $this->mch_id = $value;
    }

    public function setMchSecretKey(string $value): void
    {
        $this->mch_secret_key = $value;
    }

    public function setMchSecretCert(string $value): void
    {
        $this->mch_secret_cert = $value;
    }

    public function setMchPublicCertPath(string $value): void
    {
        $this->mch_public_cert_path = $value;
    }

    public function setNotifyUrl(string $value): void
    {
        $this->notify_url = $value;
    }

    public function setMchSecretKeyV2(?string $value): void
    {
        $this->mch_secret_key_v2 = $value;
    }

    public function setMpAppId(?string $value): void
    {
        $this->mp_app_id = $value;
    }

    public function setMiniAppId(?string $value): void
    {
        $this->mini_app_id = $value;
    }

    public function setAppId(?string $value): void
    {
        $this->app_id = $value;
    }

    public function setSubMchId(?string $value): void
    {
        $this->sub_mch_id = $value;
    }

    public function setSubMpAppId(?string $value): void
    {
        $this->sub_mp_app_id = $value;
    }

    public function setSubMiniAppId(?string $value): void
    {
        $this->sub_mini_app_id = $value;
    }

    public function setSubAppId(?string $value): void
    {
        $this->sub_app_id = $value;
    }

    public function setWechatPublicCertPath(array $value): void
    {
        $this->wechat_public_cert_path = $value;
    }

    public function setMiniAppKeyVirtualPay(?string $value): void
    {
        $this->mini_app_key_virtual_pay = $value;
    }

    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    public function getMchId(): string
    {
        return $this->mch_id;
    }

    public function getMchSecretKey(): string
    {
        return $this->mch_secret_key;
    }

    public function getMchSecretCert(): string
    {
        return $this->mch_secret_cert;
    }

    public function getMchPublicCertPath(): string
    {
        return $this->mch_public_cert_path;
    }

    public function getNotifyUrl(): string
    {
        return $this->notify_url;
    }

    public function getMchSecretKeyV2(): ?string
    {
        return $this->mch_secret_key_v2;
    }

    public function getMpAppId(): ?string
    {
        return $this->mp_app_id;
    }

    public function getMiniAppId(): ?string
    {
        return $this->mini_app_id;
    }

    public function getAppId(): ?string
    {
        return $this->app_id;
    }

    public function getSubMchId(): ?string
    {
        return $this->sub_mch_id;
    }

    public function getSubMpAppId(): ?string
    {
        return $this->sub_mp_app_id;
    }

    public function getSubMiniAppId(): ?string
    {
        return $this->sub_mini_app_id;
    }

    public function getSubAppId(): ?string
    {
        return $this->sub_app_id;
    }

    public function getMiniAppKeyVirtualPay(): ?string
    {
        return $this->mini_app_key_virtual_pay;
    }

    /**
     * @return array<string, string>
     */
    public function getWechatPublicCertPath(): array
    {
        return $this->wechat_public_cert_path;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * 优先从 CertManager 获取，fallback 到配置文件.
     */
    public function getPublicKeyBySerial(string $serialNo): ?string
    {
        $cert = CertManager::getBySerial('wechat', $this->tenant, $serialNo);

        if (null !== $cert) {
            return $cert;
        }

        return $this->wechat_public_cert_path[$serialNo] ?? null;
    }

    /**
     * 合并配置文件 wechat_public_cert_path 和 CertManager 缓存.
     *
     * @return array<string, string>
     */
    public function getAllPublicCerts(): array
    {
        $fromCertManager = CertManager::getAllBySerial('wechat', $this->tenant);

        return array_merge($this->wechat_public_cert_path, $fromCertManager);
    }

    /**
     * 调用 CertManager 保存证书.
     */
    public function setPublicCertBySerial(string $serialNo, string $cert): void
    {
        CertManager::setBySerial('wechat', $this->tenant, $serialNo, $cert);
    }

    /**
     * 校验 mch_secret_key_v2 是否存在.
     *
     * @throws InvalidConfigException
     */
    public function validateForV2(): void
    {
        if (empty($this->mch_secret_key_v2)) {
            throw new InvalidConfigException(
                Exception::CONFIG_WECHAT_INVALID,
                '配置异常: 缺少微信配置 -- [mch_secret_key_v2]'
            );
        }
    }

    /**
     * 校验 mp_app_id 是否存在.
     *
     * @throws InvalidConfigException
     */
    public function validateForMp(): void
    {
        if (empty($this->mp_app_id)) {
            throw new InvalidConfigException(
                Exception::CONFIG_WECHAT_INVALID,
                '配置异常: 缺少微信配置 -- [mp_app_id]'
            );
        }
    }

    /**
     * 校验 mini_app_id 是否存在.
     *
     * @throws InvalidConfigException
     */
    public function validateForMini(): void
    {
        if (empty($this->mini_app_id)) {
            throw new InvalidConfigException(
                Exception::CONFIG_WECHAT_INVALID,
                '配置异常: 缺少微信配置 -- [mini_app_id]'
            );
        }
    }

    protected function validateRequired(): void
    {
        $required = ['mch_id', 'mch_secret_key', 'mch_secret_cert', 'mch_public_cert_path'];

        foreach ($required as $prop) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(
                    Exception::CONFIG_WECHAT_INVALID,
                    "配置异常: 缺少微信配置 -- [{$prop}]"
                );
            }
        }

        if (Wechat::MCH_SECRET_KEY_LENGTH_BYTE !== strlen($this->mch_secret_key)) {
            throw new InvalidConfigException(
                Exception::CONFIG_WECHAT_INVALID,
                '配置异常: mch_secret_key 长度应为 32 字节'
            );
        }

        if (Pay::MODE_SERVICE === $this->mode && empty($this->sub_mch_id)) {
            throw new InvalidConfigException(
                Exception::CONFIG_WECHAT_INVALID,
                '配置异常: 服务商模式下缺少 [sub_mch_id]'
            );
        }
    }
}

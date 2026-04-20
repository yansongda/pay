<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\CertManager;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Supports\Config as BaseConfig;

class WechatConfig extends BaseConfig implements ProviderConfigInterface
{
    private string $tenant;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(array $values, string $tenant = 'default')
    {
        parent::__construct($values);

        $this->tenant = $tenant;

        $this->validateRequired();
        $this->validateMchSecretKeyLength();
        $this->validateServiceMode();
    }

    public function getTenant(): string
    {
        return $this->tenant;
    }

    public function getMchId(): string
    {
        return $this->get('mch_id', '');
    }

    public function getMchSecretKey(): string
    {
        return $this->get('mch_secret_key', '');
    }

    /**
     * 返回原始值（路径或内容）.
     */
    public function getMchSecretCert(): string
    {
        return $this->get('mch_secret_cert', '');
    }

    public function getMchPublicCertPath(): string
    {
        return $this->get('mch_public_cert_path', '');
    }

    public function getNotifyUrl(): ?string
    {
        return $this->get('notify_url');
    }

    public function getMchSecretKeyV2(): ?string
    {
        return $this->get('mch_secret_key_v2');
    }

    public function getMpAppId(): ?string
    {
        return $this->get('mp_app_id');
    }

    public function getMiniAppId(): ?string
    {
        return $this->get('mini_app_id');
    }

    public function getAppId(): ?string
    {
        return $this->get('app_id');
    }

    public function getSubMchId(): ?string
    {
        return $this->get('sub_mch_id');
    }

    public function getSubMpAppId(): ?string
    {
        return $this->get('sub_mp_app_id');
    }

    public function getSubMiniAppId(): ?string
    {
        return $this->get('sub_mini_app_id');
    }

    public function getSubAppId(): ?string
    {
        return $this->get('sub_app_id');
    }

    /**
     * 默认返回 MODE_NORMAL.
     */
    public function getMode(): int
    {
        return $this->get('mode', Pay::MODE_NORMAL);
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

        return $this->get('wechat_public_cert_path.'.$serialNo);
    }

    /**
     * 合合配置文件 wechat_public_cert_path 和 CertManager 缓存.
     *
     * @return array<string, string>
     */
    public function getAllPublicCerts(): array
    {
        $fromConfig = $this->get('wechat_public_cert_path', []);
        $fromCertManager = CertManager::getAllBySerial('wechat', $this->tenant);

        return array_merge($fromConfig, $fromCertManager);
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
        if (empty($this->getMchSecretKeyV2())) {
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
        if (empty($this->getMpAppId())) {
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
        if (empty($this->getMiniAppId())) {
            throw new InvalidConfigException(
                Exception::CONFIG_WECHAT_INVALID,
                '配置异常: 缺少微信配置 -- [mini_app_id]'
            );
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function validateRequired(): void
    {
        $required = ['mch_id', 'mch_secret_key', 'mch_secret_cert', 'mch_public_cert_path', 'notify_url'];

        foreach ($required as $key) {
            if (empty($this->get($key))) {
                throw new InvalidConfigException(
                    Exception::CONFIG_WECHAT_INVALID,
                    "配置异常: 缺少微信配置 -- [{$key}]"
                );
            }
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function validateMchSecretKeyLength(): void
    {
        $key = $this->getMchSecretKey();

        if (Wechat::MCH_SECRET_KEY_LENGTH_BYTE !== strlen($key)) {
            throw new InvalidConfigException(
                Exception::CONFIG_WECHAT_INVALID,
                '配置异常: mch_secret_key 长度应为 32 字节'
            );
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function validateServiceMode(): void
    {
        if (Pay::MODE_SERVICE === $this->getMode() && empty($this->getSubMchId())) {
            throw new InvalidConfigException(
                Exception::CONFIG_WECHAT_INVALID,
                '配置异常: 服务商模式下缺少 [sub_mch_id]'
            );
        }
    }
}

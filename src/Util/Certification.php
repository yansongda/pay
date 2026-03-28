<?php

declare(strict_types=1);

namespace Yansongda\Pay\Util;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\Exception;

use function Yansongda\Pay\get_public_cert;

class Certification
{
    /**
     * @throws InvalidConfigException
     */
    public static function sn(string $cert): string
    {
        $ssl = openssl_x509_parse(get_public_cert($cert));

        if (false === $ssl) {
            throw new InvalidConfigException(Exception::CONFIG_INVALID, '配置异常: 解析证书失败');
        }

        $ssl = self::formatCert($ssl);

        if (in_array($ssl['signatureTypeLN'], ['sha1WithRSAEncryption', 'sha256WithRSAEncryption'])) {
            return self::getCertSn($ssl['issuer'] ?? [], $ssl['serialNumber'] ?? '');
        }

        return '';
    }

    /**
     * @throws InvalidConfigException
     */
    public static function sns(array|string $certs): array
    {
        $results = [];

        if (is_string($certs)) {
            $certs = explode('-----END CERTIFICATE-----', get_public_cert($certs));
        }

        foreach ($certs as $cert) {
            if (empty(trim($cert))) {
                continue;
            }

            $results[] = self::sn(is_string($certs) ? ($cert.'-----END CERTIFICATE-----') : $cert);
        }

        return $results;
    }

    protected static function getCertSn(array $issuer, string $serialNumber): string
    {
        return md5(self::array2string(array_reverse($issuer)).$serialNumber);
    }

    protected static function array2string(array $array): string
    {
        $string = [];

        foreach ($array as $key => $value) {
            $string[] = $key.'='.$value;
        }

        return implode(',', $string);
    }

    protected static function formatCert(array $ssl): array
    {
        if (str_starts_with($ssl['serialNumber'] ?? '', '0x')) {
            $ssl['serialNumber'] = self::hex2dec($ssl['serialNumberHex'] ?? '');
        }

        return $ssl;
    }

    protected static function hex2dec(string $hex): string
    {
        $dec = '0';
        $len = strlen($hex);

        for ($i = 1; $i <= $len; ++$i) {
            $dec = bcadd(
                $dec,
                bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i), 0), 0),
                0
            );
        }

        return $dec;
    }
}
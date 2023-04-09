<?php

declare(strict_types=1);

namespace Yansongda\Pay\Packer;

use Yansongda\Pay\Contract\PackerInterface;

class XmlPacker implements PackerInterface
{
    public function pack(array $payload): string
    {
        $xml = '<xml>';

        foreach ($payload as $key => $val) {
            $xml .= is_numeric($val) ? '<'.$key.'>'.$val.'</'.$key.'>' :
                                       '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
        }

        $xml .= '</xml>';

        return $xml;
    }

    public function unpack(string $payload): ?array
    {
        if (empty($payload)) {
            return [];
        }

        if (PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader();
        }

        $data = json_decode(json_encode(
            simplexml_load_string($payload, 'SimpleXMLElement', LIBXML_NOCDATA),
            JSON_UNESCAPED_UNICODE
        ), true);

        if (JSON_ERROR_NONE === json_last_error()) {
            return $data;
        }

        return null;
    }
}

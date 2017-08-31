<?php

/*
 * (c) overtrue <i@overtrue.me>
 *
 * Modified By yansongda <me@yansongda.cn>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Yansongda\Pay\Traits;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

trait HasHttpRequest
{
    /**
     * Make a get request.
     *
     * @param string $endpoint
     * @param array  $query
     * @param array  $headers
     *
     * @return array
     */
    protected function get($endpoint, $query = [], $headers = [])
    {
        return $this->request('get', $endpoint, [
            'headers' => $headers,
            'query'   => $query,
        ]);
    }

    /**
     * Make a post request.
     *
     * @param string $endpoint
     * @param mixed  $params
     * @param array  $options
     *
     * @return string
     */
    protected function post($endpoint, $params = [], ...$options)
    {
        $options = isset($options[0]) ? $options[0] : [];

        if (!is_array($params)) {
            $options['body'] = $params;
        } else {
            $options['form_params'] = $params;
        }

        return $this->request('post', $endpoint, $options);
    }

    /**
     * Make a http request.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $options  http://docs.guzzlephp.org/en/latest/request-options.html
     *
     * @return array
     */
    protected function request($method, $endpoint, $options = [])
    {
        return $this->unwrapResponse($this->getHttpClient($this->getBaseOptions())->{$method}($endpoint, $options));
    }

    /**
     * Return base Guzzle options.
     *
     * @return array
     */
    protected function getBaseOptions()
    {
        $options = [
            'base_uri' => method_exists($this, 'getBaseUri') ? $this->getBaseUri() : '',
            'timeout'  => property_exists($this, 'timeout') ? $this->timeout : 5.0,
        ];

        return $options;
    }

    /**
     * Return http client.
     *
     * @param array $options
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient(array $options = [])
    {
        return new Client($options);
    }

    /**
     * Convert response contents to json.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array
     */
    protected function unwrapResponse(ResponseInterface $response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $contents = $response->getBody()->getContents();

        if (false !== stripos($contentType, 'json') || stripos($contentType, 'javascript')) {
            return json_decode($contents, true);
        } elseif (false !== stripos($contentType, 'xml')) {
            return json_decode(json_encode(simplexml_load_string($contents)), true);
        }

        return $contents;
    }
}

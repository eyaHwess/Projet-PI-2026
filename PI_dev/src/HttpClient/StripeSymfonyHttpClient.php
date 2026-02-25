<?php

namespace App\HttpClient;

use Stripe\HttpClient\ClientInterface;
use Stripe\Util\CaseInsensitiveArray;
use Stripe\Util\Util;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client HTTP Stripe utilisant Symfony HttpClient (sans cURL).
 */
class StripeSymfonyHttpClient implements ClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    /**
     * @param 'delete'|'get'|'post' $method
     * @param 'v1'|'v2' $apiMode
     */
    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
    {
        $params = Util::objectsToIds($params);

        if ('post' === $method) {
            $absUrl = Util::utf8($absUrl);
            if ($hasFile) {
                $body = $params;
            } elseif ('v2' === $apiMode) {
                $body = (\is_array($params) && 0 === \count($params)) ? null : \json_encode($params);
            } else {
                $body = Util::encodeParameters($params);
            }
        } else {
            if ($hasFile) {
                throw new \UnexpectedValueException("Unexpected. {$method} methods don't support file attachments");
            }
            if (0 !== \count($params)) {
                $encoded = Util::encodeParameters($params, $apiMode);
                $absUrl = Util::utf8("{$absUrl}?{$encoded}");
            } else {
                $absUrl = Util::utf8($absUrl);
            }
            $body = null;
        }

        $headersMap = $this->parseHeaders($headers);

        $options = [
            'headers' => $headersMap,
            'timeout' => 30,
            'max_redirects' => 0,
        ];

        if (null !== $body) {
            if (\is_string($body)) {
                $options['body'] = $body;
                if (!isset($headersMap['Content-Type'])) {
                    $options['headers']['Content-Type'] = ('v2' === $apiMode)
                        ? 'application/json'
                        : 'application/x-www-form-urlencoded';
                }
            } else {
                $options['body'] = $body;
            }
        }

        $response = $this->httpClient->request(
            \strtoupper($method),
            $absUrl,
            $options
        );

        $rbody = $response->getContent();
        $rcode = $response->getStatusCode();
        $rheaders = new CaseInsensitiveArray();

        foreach ($response->getHeaders() as $name => $values) {
            $rheaders[$name] = \implode(', ', $values);
        }

        return [$rbody, $rcode, $rheaders];
    }

    private function parseHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $header) {
            if (false !== \strpos($header, ':')) {
                [$key, $value] = \explode(':', $header, 2);
                $result[\trim($key)] = \trim($value);
            }
        }
        return $result;
    }
}

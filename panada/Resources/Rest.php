<?php

declare(strict_types=1);

/**
 * Panada Restful class.
 *
 * As a client (needs the curl extension):
 *
 *   $rest = new \Resources\Rest();
 *   $rest->setRequestAuthorization($token, 'Bearer');
 *   $body = $rest->sendRequest('https://api.example.com/items', 'POST', ['name' => 'Kopi']);
 *   $status = $rest->getResponseStatus();
 *
 * As a server:
 *
 *   $data = $rest->getRequest();          // PUT/PATCH/DELETE body too, form or JSON
 *   $rest->setResponseHeader(201);
 *   return $rest->wrapResponseOutput(['id' => 5]);
 *
 * @link	http://panadaframework.com/
 *
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.1
 */

namespace Resources;

class Rest
{
    public string $requestMethod;
    public int $responseStatus = 0;
    public array $requestData = [];
    public bool $responseOutputHeader = false;
    public int $timeout = 30;
    public bool $curlSSLVerifypeer = true;
    public array $requestHeaders = ['User-Agent' => 'Palakar Framework REST API/0.2'];

    /** Request headers curl sent last time, handy for debugging. */
    public string|false $headerSent = false;

    public function __construct()
    {
        $this->requestMethod = Request::method();
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    public function getResponseStatus(): int
    {
        return $this->responseStatus;
    }

    public function getRequestData(): array
    {
        return $this->requestData;
    }

    /**
     * Data sent by the client: the query string for GET, the form (or JSON
     * body) for POST, and the body for PUT, PATCH, DELETE ...
     */
    public function getRequest(): array
    {
        return $this->requestData = match ($this->requestMethod) {
            'GET', 'HEAD' => $_GET,
            'POST' => $_POST ?: $this->getPHPInput(),
            default => $this->getPHPInput(),
        };
    }

    /**
     * Get client file(s) submitted by POST.
     */
    public function getFiles(): array
    {
        return $_FILES;
    }

    /**
     * Get client request headers, e.g. ['User-Agent' => '...'].
     */
    public function getClientHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $val) {
            if (str_starts_with((string) $key, 'HTTP_')) {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$key] = $val;
            }
        }

        return $headers;
    }

    /**
     * The request body, as JSON or as a form.
     */
    private function getPHPInput(): array
    {
        $input = (string) file_get_contents('php://input');

        if (str_contains(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'json')) {
            $data = json_decode($input, true);

            return is_array($data) ? $data : [];
        }

        parse_str($input, $data);

        return $data;
    }

    /**
     * Add HTTP request headers: setRequestHeaders(['Accept' => 'application/json']).
     */
    public function setRequestHeaders(array $options = []): void
    {
        $this->requestHeaders = array_merge($this->requestHeaders, $options);
    }

    /**
     * @param string $type Basic, Bearer, ...
     */
    public function setRequestAuthorization(string $signature, string $type = 'Basic'): void
    {
        $this->setRequestHeaders(['Authorization' => $type . ' ' . $signature]);
    }

    /**
     * Send an HTTP request.
     *
     * @param array|string|null $data query string for GET, body otherwise
     *
     * @return string|false the response body (maybe ''), false when the
     *                      request itself failed (no connection, timeout)
     */
    public function sendRequest(string $uri, string $method = 'GET', array|string|null $data = null): string|false
    {
        if (!extension_loaded('curl')) {
            throw new RunException('Curl extension that required by Rest Resource is not available.');
        }

        $method = strtoupper($method);

        if (is_array($data)) {
            $data = http_build_query($data);
        }

        if ($method === 'GET' && $data) {
            $uri .= (parse_url($uri, PHP_URL_QUERY) ? '&' : '?') . $data;
        }

        $headers = [];

        foreach ($this->requestHeaders as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        $curl = curl_init($uri);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => $this->curlSSLVerifypeer,
            CURLOPT_HEADER => $this->responseOutputHeader,
            CURLOPT_HTTPHEADER => $headers,
            CURLINFO_HEADER_OUT => true,
        ]);

        if ($method === 'HEAD') {
            curl_setopt($curl, CURLOPT_NOBODY, true);
        } elseif ($method !== 'GET') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data ?? '');
        }

        $contents = curl_exec($curl);
        $this->responseStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT);

        return is_string($contents) ? $contents : false;
    }

    /**
     * Set the HTTP status code of the response.
     */
    public function setResponseHeader(int $code = 200): void
    {
        Response::$statusCode = $code;
    }

    /**
     * Encode $data as JSON (default) or XML and set the Content-Type.
     */
    public function wrapResponseOutput(mixed $data, string $format = 'json', string $ContentType = 'application'): string
    {
        Response::$headers['Content-Type'] = $ContentType . '/' . $format . '; charset=utf-8';

        if ($format === 'xml') {
            return (string) Tools::xmlEncode($data);
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Http;

use PHPUnit\Framework\TestCase;

/**
 * Starts PHP's built-in web server and sends real HTTP requests to it.
 */
abstract class HttpTestCase extends TestCase
{
    /** @var array<string, array{process: resource, url: string}> */
    private static array $servers = [];

    /** Cookies kept between requests of one test, like a browser. */
    protected array $cookies = [];

    public static function tearDownAfterClass(): void
    {
        foreach (self::$servers as $server) {
            proc_terminate($server['process']);
            proc_close($server['process']);
        }

        self::$servers = [];
    }

    protected function setUp(): void
    {
        $this->cookies = [];
    }

    /**
     * @param string $public folder with index.php
     */
    protected static function server(string $public, array $env = []): string
    {
        $key = $public.'|'.json_encode($env);

        if (isset(self::$servers[$key])) {
            return self::$servers[$key]['url'];
        }

        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int) substr(strrchr((string) stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);

        $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $process = proc_open(
            [PHP_BINARY, '-d', 'opcache.enable=0', '-S', '127.0.0.1:'.$port, '-t', $public, $public.'/index.php'],
            [0 => ['pipe', 'r'], 1 => ['file', $null, 'w'], 2 => ['file', $null, 'w']],
            $pipes,
            null,
            $env + getenv()
        );

        for ($i = 0; $i < 50; $i++) {
            if ($connection = @fsockopen('127.0.0.1', $port)) {
                fclose($connection);
                break;
            }

            usleep(100_000);
        }

        $url = 'http://127.0.0.1:'.$port;
        self::$servers[$key] = ['process' => $process, 'url' => $url];

        return $url;
    }

    /**
     * @return array{status: int, headers: array<string, list<string>>, body: string}
     */
    protected function request(string $url, string $method = 'GET', array $data = [], array $headers = []): array
    {
        $responseHeaders = [];
        $curl = curl_init($url);

        if ($this->cookies !== []) {
            $headers[] = 'Cookie: '.implode('; ', array_map(fn ($k, $v) => $k.'='.$v, array_keys($this->cookies), $this->cookies));
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HEADERFUNCTION => function ($curl, string $line) use (&$responseHeaders): int {
                if (str_contains($line, ':')) {
                    [$name, $value] = explode(':', $line, 2);
                    $responseHeaders[strtolower(trim($name))][] = trim($value);
                }

                return strlen($line);
            },
        ]);

        if ($data !== []) {
            // A CURLFile makes it a multipart upload, which needs the raw array.
            $upload = array_filter($data, fn ($value) => $value instanceof \CURLFile) !== [];
            curl_setopt($curl, CURLOPT_POSTFIELDS, $upload ? $data : http_build_query($data));
        }

        $body = (string) curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        foreach ($responseHeaders['set-cookie'] ?? [] as $cookie) {
            [$pair] = explode(';', $cookie, 2);
            [$name, $value] = explode('=', $pair, 2);
            $this->cookies[$name] = $value;
        }

        return ['status' => $status, 'headers' => $responseHeaders, 'body' => $body];
    }

    protected function csrfToken(string $html): string
    {
        $this->assertSame(1, preg_match('/name="_token" value="([a-f0-9]{64})"/', $html, $match), 'No CSRF field in page');

        return $match[1];
    }
}

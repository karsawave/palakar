<?php

declare(strict_types=1);

namespace Tests\Http;

use Resources\Rest;

/**
 * Rest as a client (this process) talking to Rest on the server side
 * (tests/Fixtures/app/Controllers/Api.php, excluded from CSRF).
 */
final class RestTest extends HttpTestCase
{
    private function url(string $path): string
    {
        return self::server(dirname(__DIR__) . '/Fixtures/public') . '/api/' . $path;
    }

    private function send(string $method, array|string|null $data = null, array $headers = []): array
    {
        $rest = new Rest();
        $rest->setRequestHeaders($headers);
        $body = $rest->sendRequest($this->url('inspect'), $method, $data);

        $this->assertSame(200, $rest->getResponseStatus(), (string) $body);

        return json_decode((string) $body, true);
    }

    public function testEveryMethodArrivesWithItsData(): void
    {
        $this->assertSame(['method' => 'GET', 'data' => ['q' => 'kopi'], 'agent' => 'Palakar Framework REST API/0.3'], $this->send('GET', ['q' => 'kopi']));
        $this->assertSame(['name' => 'Budi'], $this->send('POST', ['name' => 'Budi'])['data']);
        $this->assertSame(['name' => 'Ani'], $this->send('PUT', ['name' => 'Ani'])['data']);

        $patch = $this->send('PATCH', '{"score":10}', ['Content-Type' => 'application/json']);
        $this->assertSame(['method' => 'PATCH', 'data' => ['score' => 10]], array_slice($patch, 0, 2));

        $this->assertSame('DELETE', $this->send('delete')['method']);
    }

    public function testHeadersAreBuiltPerRequest(): void
    {
        $rest = new Rest();
        $rest->setRequestAuthorization('secret', 'Bearer');
        $rest->sendRequest($this->url('inspect'));
        $rest->sendRequest($this->url('inspect'));

        $this->assertSame(1, substr_count((string) $rest->headerSent, 'Authorization: Bearer secret'));
    }

    public function testEmptyBodyIsNotAFailure(): void
    {
        $rest = new Rest();

        $this->assertSame('', $rest->sendRequest($this->url('nothing')));
        $this->assertSame(200, $rest->getResponseStatus());

        $rest->timeout = 2;
        $this->assertFalse($rest->sendRequest('http://127.0.0.1:1/'), 'No server listening');
    }

    public function testResponseHelpersKeepTheStatusCode(): void
    {
        $created = $this->request($this->url('created'));
        $this->assertSame(201, $created['status']);
        $this->assertSame(['application/json; charset=utf-8'], $created['headers']['content-type']);
        $this->assertSame('{"id":5,"path":"a/b"}', $created['body']);

        $accepted = $this->request($this->url('accepted'));
        $this->assertSame(202, $accepted['status']);
        $this->assertSame('{"queued":true}', $accepted['body']);
    }
}

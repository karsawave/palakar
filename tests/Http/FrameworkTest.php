<?php

declare(strict_types=1);

namespace Tests\Http;

/**
 * Behaviour of the framework, using the fixture app in tests/Fixtures.
 */
final class FrameworkTest extends HttpTestCase
{
    private static string $public;

    public static function setUpBeforeClass(): void
    {
        self::$public = dirname(__DIR__) . '/Fixtures/public';
    }

    private function url(array $env = []): string
    {
        return self::server(self::$public, $env);
    }

    public function testRoutingBasics(): void
    {
        $url = $this->url();

        $this->assertSame('fixture home', $this->request($url . '/')['body']);
        $this->assertSame('printed with echo', $this->request($url . '/home/echoed')['body']);
        $this->assertSame('id=15 int', $this->request($url . '/home/show/15')['body']);
        $this->assertSame(404, $this->request($url . '/home/show/abc')['status']);
        $this->assertSame(404, $this->request($url . '/home/show')['status']);

        $json = $this->request($url . '/home/data');
        $this->assertSame('{"name":"Panada","version":2}', $json['body']);
        $this->assertStringStartsWith('application/json', $json['headers']['content-type'][0]);
    }

    public function testRunsWithoutComposer(): void
    {
        $url = $this->url(['PANADA_NO_COMPOSER' => '1']);

        $this->assertSame('fixture home', $this->request($url . '/')['body']);
        $this->assertSame('id=7 int', $this->request($url . '/home/show/7')['body']);
    }

    public function testDebugErrorPageUsesWhoopsWhenInstalled(): void
    {
        $response = $this->request($this->url() . '/boom');

        $this->assertSame(500, $response['status']);
        $this->assertStringContainsString('Palakar error', $response['body']);
        $this->assertStringContainsString('Kaboom', $response['body']);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $response['body']);

        $json = $this->request($this->url() . '/boom', headers: ['Accept: application/json']);
        $this->assertSame(500, $json['status']);
        $this->assertStringContainsString('Kaboom', json_decode($json['body'], true)['error']['message']);
    }

    public function testDebugErrorPageWithoutWhoops(): void
    {
        $url = $this->url(['PANADA_NO_COMPOSER' => '1']);

        foreach (['/boom' => 'Kaboom', '/boom/warning' => 'Undefined array key', '/boom/fatal' => 'Allowed memory size'] as $path => $message) {
            $response = $this->request($url . $path);

            $this->assertSame(500, $response['status'], $path);
            $this->assertStringContainsString('Runtime Error', $response['body'], $path);
            $this->assertStringContainsString($message, $response['body'], $path);
            $this->assertStringContainsString('Source code', $response['body'], $path);
            $this->assertStringNotContainsString('<script>alert(1)</script>', $response['body'], $path);
        }
    }

    public function testProductionErrorPageLeaksNothing(): void
    {
        $url = $this->url(['PANADA_DEBUG' => '0']);

        foreach (['/boom', '/boom/warning', '/boom/fatal'] as $path) {
            $response = $this->request($url . $path);

            $this->assertSame(500, $response['status'], $path);
            $this->assertStringContainsString('Something went wrong', $response['body'], $path);
            $this->assertStringNotContainsString('Kaboom', $response['body'], $path);
            $this->assertStringNotContainsString('Boom.php', $response['body'], $path);
            $this->assertStringNotContainsString('Undefined', $response['body'], $path);
        }
    }

    public function testHttpExceptionStatus(): void
    {
        $response = $this->request($this->url() . '/boom/forbidden');

        $this->assertSame(403, $response['status']);
        $this->assertStringContainsString('Members only', $response['body']);
    }

    public function testCsrfProtection(): void
    {
        $url = $this->url();
        $token = $this->csrfToken($this->request($url . '/form')['body']);

        $this->assertSame(403, $this->request($url . '/form/save', 'POST', ['a' => 1])['status']);
        $this->assertSame(403, $this->request($url . '/form/save', 'PUT')['status']);
        $this->assertSame('saved', $this->request($url . '/form/save', 'POST', ['_token' => $token])['body']);
        $this->assertSame('saved', $this->request($url . '/form/save', 'DELETE', headers: ['X-CSRF-Token: ' . $token])['body']);

        $this->cookies = [];
        $this->assertSame('stored without csrf', $this->request($url . '/api/store', 'POST', ['a' => 1])['body']);
    }

    public function testSessionCookieFlagsAndRegeneration(): void
    {
        $url = $this->url();

        $this->request($url . '/auth/whoami');
        $before = $this->cookies['PAN_SID'];

        $login = $this->request($url . '/auth/login/budi');
        $this->assertNotSame($before, $this->cookies['PAN_SID'], 'Session id must change at login');
        $this->assertStringContainsString('HttpOnly', end($login['headers']['set-cookie']));
        $this->assertSame('budi', $this->request($url . '/auth/whoami')['body']);

        $this->request($url . '/auth/logout');
        $this->assertSame('', $this->request($url . '/auth/whoami')['body']);
    }

    public function testUnknownSessionIdIsRejected(): void
    {
        $this->cookies = ['PAN_SID' => 'attackerchosenid1234567890'];
        $this->request($this->url() . '/auth/whoami');

        $this->assertNotSame('attackerchosenid1234567890', $this->cookies['PAN_SID']);
    }

    public function testSecureFlagBehindTrustedProxy(): void
    {
        $proxyHeader = ['X-Forwarded-Proto: https'];

        $untrusted = $this->request($this->url() . '/auth/whoami', headers: $proxyHeader);
        $this->assertStringNotContainsString('secure', strtolower($untrusted['headers']['set-cookie'][0]));

        $this->cookies = [];
        $trusted = $this->request($this->url(['PANADA_TRUST_PROXY' => '1']) . '/auth/whoami', headers: $proxyHeader);
        $this->assertStringContainsString('secure', strtolower($trusted['headers']['set-cookie'][0]));
    }

    public function testDatabaseSessionDriver(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'panada');
        $pdo = new \PDO('sqlite:' . $file);
        $pdo->exec('CREATE TABLE sessions (session_id VARCHAR(128) PRIMARY KEY, session_data TEXT NOT NULL, session_expiration INTEGER NOT NULL)');

        $url = $this->url(['PANADA_SESSION_DRIVER' => 'database', 'PANADA_TEST_SQLITE' => $file]);
        $this->request($url . '/auth/login/ani');

        $this->assertSame('ani', $this->request($url . '/auth/whoami')['body']);
        $this->assertStringContainsString('ani', (string) $pdo->query('SELECT session_data FROM sessions')->fetchColumn());

        $pdo = null;
        @unlink($file);
    }

    public function testSignedCookieSessionDriver(): void
    {
        $url = $this->url(['PANADA_SESSION_DRIVER' => 'cookie']);
        $this->request($url . '/auth/login/citra');

        $this->assertSame('citra', $this->request($url . '/auth/whoami')['body']);

        // Flip one character of the payload: the signature no longer matches.
        $cookie = $this->cookies['PAN_SID'];
        $this->cookies['PAN_SID'] = ($cookie[0] === 'a' ? 'b' : 'a') . substr($cookie, 1);
        $this->assertSame('', $this->request($url . '/auth/whoami')['body']);
    }
}

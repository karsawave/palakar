<?php

declare(strict_types=1);

namespace Tests\Http;

/**
 * The application that ships in app/ and public/.
 */
final class SkeletonAppTest extends HttpTestCase
{
    private static string $url;
    private static bool $createdDatabase = false;

    public static function setUpBeforeClass(): void
    {
        $database = dirname(__DIR__, 2) . '/app/storage/database.sqlite';
        self::$createdDatabase = !is_file($database);
        self::$url = self::server(dirname(__DIR__, 2) . '/public') . '/index.php';
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (self::$createdDatabase) {
            @unlink(dirname(__DIR__, 2) . '/app/storage/database.sqlite');
        }
    }

    public function testHomePage(): void
    {
        $response = $this->request(self::$url);

        $this->assertSame(200, $response['status']);
        $this->assertStringContainsString('Palakar has been installed successfully', $response['body']);
        $this->assertSame(['nosniff'], $response['headers']['x-content-type-options']);
        $this->assertSame(['SAMEORIGIN'], $response['headers']['x-frame-options']);
        $this->assertSame(['strict-origin-when-cross-origin'], $response['headers']['referrer-policy']);
    }

    public function testInheritedControllerMethodsAreNotRoutable(): void
    {
        foreach (['/home/output/errors', '/home/redirect', '/home/location', '/home/__construct', '/home/outputJSON'] as $path) {
            $this->assertSame(404, $this->request(self::$url . $path)['status'], $path);
        }
    }

    public function testNotFoundPageEscapesTheUrl(): void
    {
        $response = $this->request(self::$url . '/<script>alert(1)</script>');

        $this->assertSame(404, $response['status']);
        $this->assertStringNotContainsString('<script>alert(1)', $response['body']);
        $this->assertStringContainsString('&lt;script&gt;', $response['body']);
    }

    public function testGuestbookFlow(): void
    {
        $page = $this->request(self::$url . '/guestbook');
        $this->assertSame(200, $page['status']);

        $cookie = $page['headers']['set-cookie'][0];
        $this->assertStringContainsString('HttpOnly', $cookie);
        $this->assertStringContainsString('SameSite=Lax', $cookie);
        $this->assertStringNotContainsString('secure', strtolower($cookie));

        $token = $this->csrfToken($page['body']);
        $entry = ['name' => 'Budi', 'email' => 'budi@example.com', 'message' => '<script>alert(1)</script> it\'s\'); DROP TABLE guestbook; --'];

        $this->assertSame(403, $this->request(self::$url . '/guestbook', 'POST', $entry)['status'], 'No token');
        $this->assertSame(403, $this->request(self::$url . '/guestbook', 'POST', ['_token' => str_repeat('0', 64)] + $entry)['status'], 'Wrong token');

        $invalid = $this->request(self::$url . '/guestbook', 'POST', ['_token' => $token, 'name' => 'Budi']);
        $this->assertSame(200, $invalid['status']);
        $this->assertStringContainsString('Email can not be empty', $invalid['body']);

        $this->assertSame(302, $this->request(self::$url . '/guestbook', 'POST', ['_token' => $token] + $entry)['status']);

        $list = $this->request(self::$url . '/guestbook');
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt; it&#039;s&#039;); DROP TABLE guestbook; --', $list['body']);
        $this->assertStringNotContainsString('<script>alert(1)', $list['body']);
    }
}

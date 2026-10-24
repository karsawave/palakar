<?php

declare(strict_types=1);

namespace Tests\Unit;

use Drivers\Session\Cookie;
use Drivers\Session\DatabaseHandler;
use Drivers\Session\Native;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Resources\Database;
use Resources\RunException;
use Resources\Session;

final class SessionTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['HTTPS'], $_COOKIE['PAN_SID']);
    }

    public function testCookieOptionsDefaults(): void
    {
        $this->assertSame(
            ['lifetime' => 0, 'path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax'],
            Native::cookieOptions([])
        );
    }

    public function testSecureAutoFollowsHttps(): void
    {
        $_SERVER['HTTPS'] = 'on';

        $this->assertTrue(Native::cookieOptions(['cookieSecure' => 'auto'])['secure']);
        $this->assertFalse(Native::cookieOptions(['cookieSecure' => false])['secure']);
    }

    public function testSameSiteNoneForcesSecure(): void
    {
        $options = Native::cookieOptions(['cookieSameSite' => 'none', 'cookieSecure' => false]);

        $this->assertSame('None', $options['samesite']);
        $this->assertTrue($options['secure']);
    }

    public function testInvalidSameSiteIsRefused(): void
    {
        $this->expectException(RunException::class);
        Native::cookieOptions(['cookieSameSite' => 'sometimes']);
    }

    #[RunInSeparateProcess]
    public function testNativeSessionAppliesHardenedSettings(): void
    {
        $session = new Session();
        $session->setValue(['user' => 'budi', 'role' => 'admin']);
        $session->deleteValue('role');

        $params = session_get_cookie_params();
        $this->assertSame(PHP_SESSION_ACTIVE, session_status());
        $this->assertTrue($params['httponly']);
        $this->assertSame('Lax', $params['samesite']);
        $this->assertSame('1', ini_get('session.use_strict_mode'));
        $this->assertSame('1', ini_get('session.use_only_cookies'));
        $this->assertSame('PAN_SID', session_name());
        $this->assertSame(['user' => 'budi'], $session->getAllValue());
        $this->assertFalse($session->getValue('role'));

        $oldId = session_id();
        $session->regenerateId();
        $this->assertNotSame($oldId, session_id());
        $this->assertSame('budi', $session->getValue('user'));
    }

    public function testCookieDriverRoundTripAndTamperDetection(): void
    {
        $config = ['secretKey' => str_repeat('s', 40), 'expiration' => 60];

        $writer = new Cookie($config);
        $writer->setValue('user_id', 15);
        $cookie = $_COOKIE['PAN_SID'];

        $_COOKIE['PAN_SID'] = $cookie;
        $this->assertSame(15, (new Cookie($config))->getValue('user_id'));

        // Change the payload, keep the signature: must be rejected.
        [$payload, $signature] = explode('.', $cookie);
        $forged = rtrim(strtr(base64_encode(json_encode(['values' => ['user_id' => 1], 'exp' => time() + 60])), '+/', '-_'), '=');
        $_COOKIE['PAN_SID'] = $forged.'.'.$signature;
        $this->assertFalse((new Cookie($config))->getValue('user_id'));

        // Signed with another key: rejected.
        $_COOKIE['PAN_SID'] = $cookie;
        $this->assertFalse((new Cookie(['secretKey' => str_repeat('x', 40)] + $config))->getValue('user_id'));
    }

    public function testEncryptedCookieDriverHidesValues(): void
    {
        $config = ['secretKey' => str_repeat('s', 40), 'isEncrypt' => true];

        (new Cookie($config))->setValue('email', 'budi@example.com');

        $this->assertStringNotContainsString('budi', (string) base64_decode(strtr($_COOKIE['PAN_SID'], '-_', '+/')));
        $this->assertSame('budi@example.com', (new Cookie($config))->getValue('email'));
    }

    public function testCookieDriverNeedsARealSecret(): void
    {
        $this->expectException(RunException::class);
        new Cookie(['secretKey' => '_put_your_random_string_here_']);
    }

    public function testDatabaseHandler(): void
    {
        $db = new Database();
        $db->query('CREATE TABLE sessions (session_id VARCHAR(128) PRIMARY KEY, session_data TEXT NOT NULL, session_expiration INTEGER NOT NULL)');
        $handler = new DatabaseHandler($db, 'sessions', 60);

        $this->assertSame('', $handler->read('abc'));
        $this->assertFalse($handler->validateId('abc'));

        $this->assertTrue($handler->write('abc', 'user|s:4:"budi";'));
        $this->assertTrue($handler->write('abc', 'user|s:3:"ani";'));
        $this->assertSame('user|s:3:"ani";', $handler->read('abc'));
        $this->assertTrue($handler->validateId('abc'));

        $db->update('sessions', ['session_expiration' => time() - 1], ['session_id' => 'abc']);
        $this->assertSame('', $handler->read('abc'));
        $this->assertSame(1, $handler->gc(60));

        $handler->write('def', 'x');
        $handler->destroy('def');
        $this->assertSame('', $handler->read('def'));
    }
}

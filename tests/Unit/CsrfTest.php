<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Resources\Csrf;
use Resources\HttpException;

final class CsrfTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_METHOD'], $_POST['_token'], $_SERVER['HTTP_X_CSRF_TOKEN']);
    }

    #[RunInSeparateProcess]
    public function testTokenIsStablePerSessionAndRegenerates(): void
    {
        $token = Csrf::token();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertSame($token, Csrf::token());
        $this->assertStringContainsString('name="_token" value="'.$token.'"', csrf_field());

        $new = Csrf::regenerate();
        $this->assertNotSame($token, $new);
        $this->assertFalse(Csrf::isValid($token));
        $this->assertTrue(Csrf::isValid($new));
    }

    #[RunInSeparateProcess]
    public function testSafeMethodsAndExceptedPathsAreNotChecked(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Csrf::verifyRequest('form/save');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        Csrf::verifyRequest('api/orders');   // 'api/*' is excepted in the fixture config

        $this->addToAssertionCount(2);
    }

    #[RunInSeparateProcess]
    public function testPostWithoutValidTokenIsForbidden(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        foreach ([null, '', 'deadbeef', str_repeat('a', 64)] as $token) {
            if ($token !== null) {
                $_POST['_token'] = $token;
            }

            try {
                Csrf::verifyRequest('form/save');
                $this->fail('Token '.var_export($token, true).' was accepted');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getCode());
            }
        }
    }

    #[RunInSeparateProcess]
    public function testValidTokenFromFieldOrHeaderPasses(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_token'] = Csrf::token();
        Csrf::verifyRequest('form/save');

        unset($_POST['_token']);
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['HTTP_X_CSRF_TOKEN'] = Csrf::token();
        Csrf::verifyRequest('items/5');

        $this->addToAssertionCount(2);
    }
}

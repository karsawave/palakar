<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Resources\Request;

final class RequestTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO'], $_GET['id'], $_SERVER['SERVER_PORT']);
    }

    public function testStripTagsAttributesWorksWithoutCreateFunction(): void
    {
        $html = '<a href="/x" onclick="steal()" title="t">link</a><script>bad()</script><b>ok</b>';
        $clean = (new Request())->stripTagsAttributes($html, '<a><b>', 'href,title');

        $this->assertSame('<a href="/x" title="t">link</a>bad()<b>ok</b>', $clean);
    }

    public function testGetWithFilter(): void
    {
        $_GET['id'] = '15abc';
        $request = new Request();

        $this->assertSame('15abc', $request->get('id'));
        $this->assertFalse($request->get('id', FILTER_VALIDATE_INT));
        $this->assertFalse($request->get('missing'));
    }

    public function testHttpsDetection(): void
    {
        $this->assertFalse(Request::isSecure());

        $_SERVER['HTTPS'] = 'on';
        $this->assertTrue(Request::isSecure());
    }

    public function testForwardedProtoIsIgnoredUnlessTrusted(): void
    {
        // trustProxyHeaders is false in the fixture config (PANADA_TRUST_PROXY unset).
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $this->assertFalse(Request::isSecure());
    }
}

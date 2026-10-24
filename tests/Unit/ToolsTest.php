<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Resources\Response;
use Resources\Tools;

final class ToolsTest extends TestCase
{
    protected function tearDown(): void
    {
        Response::$statusCode = 200;
    }

    public function testXmlEncodeEscapesValuesAndNames(): void
    {
        $xml = Tools::xmlEncode([
            'name' => 'Budi & Ani <script>',
            'city' => 'Café',
            'score' => 10,
            'active' => true,
            'note' => null,
            'first name' => 'x',
            'tags' => ['php', 'kiss'],
            'owner' => (object) ['id' => 7],
        ]);

        $parsed = simplexml_load_string($xml);

        $this->assertSame('Budi & Ani <script>', (string) $parsed->name);
        $this->assertSame('Café', (string) $parsed->city);
        $this->assertSame('10', (string) $parsed->score);
        $this->assertSame('1', (string) $parsed->active);
        $this->assertSame('', (string) $parsed->note);
        $this->assertSame('x', (string) $parsed->first_name);
        $this->assertSame('kiss', (string) $parsed->tags->unknownNode_1);
        $this->assertSame('7', (string) $parsed->owner->id);
    }

    public function testXmlDecodeFromStringOrFile(): void
    {
        $xml = '<data><user><name>Budi</name></user></data>';

        $this->assertSame('Budi', (string) Tools::xmlDecode($xml)->user->name);
        $this->assertSame(['user' => ['name' => 'Budi']], Tools::xmlDecode($xml, 'array'));

        $file = sys_get_temp_dir().'/panada-tools-test.xml';
        file_put_contents($file, $xml);
        $this->assertSame('Budi', (string) Tools::xmlDecode($file)->user->name);
        unlink($file);

        $this->assertFalse(Tools::xmlDecode('<data><broken></data>'));
    }

    public function testRandomString(): void
    {
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{32}$/', Tools::getRandomString(32, false));
        $this->assertSame(12, strlen(Tools::getRandomString()));
    }

    public function testStatusHeaderGoesThroughTheResponse(): void
    {
        Tools::setStatusHeader(404);

        $this->assertSame(404, Response::$statusCode);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Resources\Encryption;
use Resources\RunException;

final class EncryptionTest extends TestCase
{
    public function testRoundTrip(): void
    {
        $encryption = new Encryption();
        $encrypted = $encryption->encrypt('panada');

        $this->assertNotSame('panada', $encrypted);
        $this->assertSame('panada', $encryption->decrypt($encrypted));
    }

    public function testSameTextGivesDifferentCiphertext(): void
    {
        $encryption = new Encryption();

        $this->assertNotSame($encryption->encrypt('panada'), $encryption->encrypt('panada'));
    }

    public function testTamperedValueIsRejected(): void
    {
        $encryption = new Encryption();
        $encrypted = $encryption->encrypt('amount=1000');
        $tampered = substr($encrypted, 0, -2) . ($encrypted[-2] === 'A' ? 'B' : 'A') . $encrypted[-1];

        $this->assertFalse($encryption->decrypt($tampered));
        $this->assertFalse($encryption->decrypt('not-base64-!!'));
        $this->assertFalse((new Encryption(str_repeat('k', 40)))->decrypt($encrypted));
    }

    public function testWeakKeyIsRefused(): void
    {
        $this->expectException(RunException::class);
        new Encryption('123');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Resources\Email;

/**
 * Sends through a fake SMTP server (tests/Fixtures/smtp.php) and checks
 * what it received. The native mail() path builds the same message.
 */
final class EmailTest extends TestCase
{
    private string $attachment;

    protected function setUp(): void
    {
        $this->attachment = str_replace('\\', '/', sys_get_temp_dir()).'/laporan bulanan.txt';
        file_put_contents($this->attachment, 'isi laporan');
    }

    protected function tearDown(): void
    {
        unlink($this->attachment);
    }

    /**
     * Run $send against a fresh fake server, return [result, transcript].
     *
     * @param callable(Email): bool $send
     */
    private function smtp(callable $send, string $mode = ''): array
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int) substr(strrchr((string) stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);

        $process = proc_open(
            [PHP_BINARY, dirname(__DIR__).'/Fixtures/smtp.php', (string) $port, $mode],
            [1 => ['pipe', 'w']],
            $pipes
        );
        $this->assertSame("ready\n", fgets($pipes[1]));

        $email = (new Email())->setOption(['smtpHost' => '127.0.0.1', 'smtpPort' => $port, 'timeoutConnection' => 5]);
        $result = $send($email);

        $transcript = (string) stream_get_contents($pipes[1]);
        proc_close($process);

        return [$result, $transcript, $email];
    }

    private function header(string $transcript, string $name): string
    {
        preg_match('/^'.$name.': (.*?)\r\n(?![ \t])/ms', $transcript, $match);

        return str_replace("\r\n", '', $match[1] ?? '');
    }

    public function testSmtpMessage(): void
    {
        [$sent, $transcript, $email] = $this->smtp(fn (Email $email) => $email
            ->setOption(['smtpUsername' => 'budi', 'smtpPassword' => 'rahasia', 'messageType' => 'html'])
            ->from('budi@example.com', 'Budi Santoso')
            ->to('ani@example.com, Citra@Example.com')
            ->cc('dewi@example.com')
            ->bcc('boss@example.com')
            ->replyTo('cs@example.com')
            ->subject('Halo 👋 dari Panada')
            ->message("<p>Laporan terlampir.</p>\n.\n<p>Salam</p>")
            ->altMessage('Laporan terlampir.')
            ->attach($this->attachment)
            ->mail());

        $this->assertTrue($sent, implode("\n", $email->printDebug(false)));

        $this->assertStringContainsString("AUTH LOGIN\r\n".base64_encode('budi')."\r\n".base64_encode('rahasia')."\r\n", $transcript);
        $this->assertStringContainsString("MAIL FROM:<budi@example.com>\r\n", $transcript);

        foreach (['ani@example.com', 'citra@example.com', 'dewi@example.com', 'boss@example.com'] as $rcpt) {
            $this->assertStringContainsString('RCPT TO:<'.$rcpt.">\r\n", $transcript);
        }

        $this->assertSame('ani@example.com, citra@example.com', $this->header($transcript, 'To'));
        $this->assertSame('Halo 👋 dari Panada', mb_decode_mimeheader($this->header($transcript, 'Subject')));
        $this->assertSame('"Budi Santoso" <budi@example.com>', $this->header($transcript, 'From'));
        $this->assertSame('cs@example.com', $this->header($transcript, 'Reply-To'));
        $this->assertNotSame('', $this->header($transcript, 'Date'));
        $this->assertMatchesRegularExpression('/^<[a-f0-9]{32}@example\.com>$/', $this->header($transcript, 'Message-ID'));
        $this->assertStringNotContainsString('Bcc:', $transcript, 'Bcc must stay hidden');

        $this->assertStringContainsString('multipart/mixed', $transcript);
        $this->assertStringContainsString('multipart/alternative', $transcript);
        $this->assertSame(2, preg_match_all('/^--Palakar-[a-f0-9]+--\r$/m', $transcript), 'Both multiparts are closed');
        $this->assertStringContainsString('filename="laporan bulanan.txt"', $transcript);
        $this->assertStringContainsString(base64_encode('isi laporan'), $transcript);
        $this->assertStringContainsString("\r\n..\r\n", $transcript, 'A line with a single dot is doubled');
        $this->assertSame(0, preg_match('/(?<!\r)\n/', $transcript), 'Only CRLF line breaks');

        $debug = implode("\n", $email->printDebug(false));
        $this->assertStringNotContainsString(base64_encode('rahasia'), $debug);

        ob_start();
        $email->printDebug();
        $this->assertStringContainsString('&lt;b&gt;fake&lt;/b&gt;', (string) ob_get_clean());
    }

    public function testHeaderInjectionIsBlocked(): void
    {
        [$sent, $transcript] = $this->smtp(fn (Email $email) => $email
            ->mail('ani@example.com', "Halo\r\nBcc: victim@example.com", 'isi', 'budi@example.com', "Budi\r\nX-Evil: 1"));

        $this->assertTrue($sent);
        $this->assertStringNotContainsString("\r\nBcc: victim", $transcript);
        $this->assertStringNotContainsString("\r\nX-Evil", $transcript);

        $email = new Email();
        $this->assertFalse($email->mail("ani@example.com\r\nBcc: victim@example.com", 'Halo', 'isi', 'budi@example.com'));
        $this->assertStringContainsString('Invalid', implode(' ', $email->printDebug(false)));
    }

    public function testFailedLoginStopsBeforeSending(): void
    {
        [$sent, $transcript] = $this->smtp(fn (Email $email) => $email
            ->setOption(['smtpUsername' => 'budi', 'smtpPassword' => 'salah'])
            ->mail('ani@example.com', 'Halo', 'isi', 'budi@example.com'), 'reject-auth');

        $this->assertFalse($sent);
        $this->assertStringNotContainsString('MAIL FROM', $transcript);
    }

    public function testTlsIsRequiredWhenAsked(): void
    {
        [$sent, $transcript] = $this->smtp(fn (Email $email) => $email
            ->setOption(['smtpSecure' => 'tls', 'smtpUsername' => 'budi', 'smtpPassword' => 'rahasia'])
            ->mail('ani@example.com', 'Halo', 'isi', 'budi@example.com'));

        $this->assertFalse($sent);
        $this->assertStringContainsString('STARTTLS', $transcript);
        $this->assertStringNotContainsString('AUTH', $transcript, 'No password over plain text');
    }

    public function testRejectedRecipientFailsTheSend(): void
    {
        [$sent, $transcript] = $this->smtp(fn (Email $email) => $email
            ->mail('ani@example.com, bad@example.com', 'Halo', 'isi', 'budi@example.com'), 'reject-rcpt');

        $this->assertFalse($sent);
        $this->assertStringNotContainsString('DATA', $transcript);
    }
}

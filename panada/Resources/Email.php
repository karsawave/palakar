<?php

declare(strict_types=1);

/**
 * Panada email API: PHP's mail() or SMTP.
 *
 *   $email = new \Resources\Email();
 *   $email->setOption([
 *       'smtpHost' => 'smtp.example.com',
 *       'smtpPort' => 587,
 *       'smtpSecure' => 'tls',          // STARTTLS; 'ssl' for port 465
 *       'smtpUsername' => 'app@example.com',
 *       'smtpPassword' => getenv('SMTP_PASSWORD'),
 *       'messageType' => 'html',
 *   ]);
 *
 *   $sent = $email->from('app@example.com', 'Toko Kopi')
 *       ->to($customer)
 *       ->subject('Pesanan diterima')
 *       ->message($this->output('emails/order', ['order' => $order]))
 *       ->attach(APP.'storage/invoice-15.pdf')
 *       ->mail();
 *
 *   if (!$sent) {
 *       error_log(implode("\n", $email->printDebug(false)));
 *   }
 *
 * Messages are UTF-8. Addresses are validated and line breaks are removed
 * from header values, so form input cannot add headers (e.g. a Bcc).
 *
 * @link	http://panadaframework.com/
 *
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>, Azhari Harahap <azhari@harahap.us>
 *
 * @since	Version 0.1
 */

namespace Resources;

class Email
{
    use Configurable;

    public array $rcptTo = [];
    public array $rcptCc = [];
    public array $rcptBcc = [];
    public ?string $replyTo = null;
    public string $subject = '';
    public string $message = '';

    /** plain | html */
    public string $messageType = 'plain';

    /** Plain text version of an html message. */
    public string $altMessage = '';

    public string $fromEmail = '';
    public string $fromName = '';

    /** native (PHP's mail()) | smtp. Setting smtpHost also means smtp. */
    public string $mailerType = 'native';

    /** 1 = High, 3 = Normal, 5 = low. */
    public int $priority = 3;

    public string $smtpHost = '';
    public int $smtpPort = 25;

    /** 'ssl' (port 465), 'tls' (STARTTLS, port 587) or false. */
    public string|false $smtpSecure = false;

    public string $smtpUsername = '';
    public string $smtpPassword = '';

    /** Host name told to the server in EHLO. */
    public string $smtpEhloHost = 'localhost';

    /** Seconds to wait for the SMTP server. */
    public int $timeoutConnection = 30;

    /** @var resource|null */
    private $smtpConnection = null;

    private array $debugMessages = [];
    private array $attachment = [];

    public function from(string $fromEmail = '', string $fromName = ''): static
    {
        $this->fromEmail = strtolower(trim($fromEmail));
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * @param string|array $rcptTo 'a@example.com, b@example.com' or an array
     */
    public function to(string|array $rcptTo = ''): static
    {
        $this->rcptTo = $this->addresses($rcptTo);

        return $this;
    }

    public function cc(string|array $rcptCc = ''): static
    {
        $this->rcptCc = $this->addresses($rcptCc);

        return $this;
    }

    public function bcc(string|array $rcptBcc = ''): static
    {
        $this->rcptBcc = $this->addresses($rcptBcc);

        return $this;
    }

    public function replyTo(?string $replyTo = null): static
    {
        $this->replyTo = $replyTo === null ? null : strtolower(trim($replyTo));

        return $this;
    }

    public function subject(string $subject = ''): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function message(string $message = ''): static
    {
        $this->message = $message;

        return $this;
    }

    public function altMessage(string $altMessage = ''): static
    {
        $this->altMessage = $altMessage;

        return $this;
    }

    /**
     * Attach a file from disk.
     */
    public function attach(string $filename = ''): static
    {
        $this->attachment[] = $filename;

        return $this;
    }

    /**
     * Send the email. The arguments are a shortcut for the methods above.
     */
    public function mail(string|array $rcptTo = '', string $subject = '', string $message = '', string $fromEmail = '', string $fromName = '', string|array $rcptCc = '', string|array $rcptBcc = ''): bool
    {
        if ($rcptTo) {
            $this->to($rcptTo);
        }

        if ($subject !== '') {
            $this->subject = $subject;
        }

        if ($message !== '') {
            $this->message = $message;
        }

        if ($fromEmail !== '') {
            $this->from($fromEmail, $fromName ?: $this->fromName);
        }

        if ($rcptCc) {
            $this->cc($rcptCc);
        }

        if ($rcptBcc) {
            $this->bcc($rcptBcc);
        }

        if (!$this->validate()) {
            return false;
        }

        [$headers, $body] = $this->build();

        if ($this->smtpHost !== '' || $this->mailerType === 'smtp') {
            $this->mailerType = 'smtp';

            return $this->smtpSend($headers, $body);
        }

        return $this->mailerNative($headers, $body);
    }

    /**
     * What happened, line by line: printDebug() prints it (escaped),
     * printDebug(false) returns the array.
     */
    public function printDebug(bool $isEcho = true): ?array
    {
        if (!$isEcho) {
            return $this->debugMessages;
        }

        echo implode('<br>', array_map('e', $this->debugMessages));

        return null;
    }

    private function addresses(string|array $emails): array
    {
        $emails = is_array($emails) ? $emails : explode(',', $emails);

        return array_values(array_filter(array_map(fn ($email) => strtolower(trim((string) $email)), $emails)));
    }

    /**
     * Every address must be valid. FILTER_VALIDATE_EMAIL also refuses line
     * breaks, so an address cannot smuggle in another header.
     */
    private function validate(): bool
    {
        if ($this->rcptTo === []) {
            $this->debugMessages[] = 'Error: No recipient';

            return false;
        }

        $addresses = [$this->fromEmail, ...$this->rcptTo, ...$this->rcptCc, ...$this->rcptBcc];

        if ($this->replyTo !== null) {
            $addresses[] = $this->replyTo;
        }

        foreach ($addresses as $address) {
            if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
                $this->debugMessages[] = 'Error: Invalid email address "'.$address.'"';

                return false;
            }
        }

        foreach ($this->attachment as $file) {
            if (!is_file($file) || !is_readable($file)) {
                $this->debugMessages[] = 'Error: Attachment '.$file.' not found';

                return false;
            }
        }

        return true;
    }

    /**
     * The message: headers (without To and Subject, which mail() adds itself)
     * and the MIME body.
     *
     * @return array{0: array<string, string>, 1: string}
     */
    private function build(): array
    {
        if ($this->messageType === 'html') {
            $text = $this->altMessage !== '' ? $this->altMessage : 'Your mail client does not support HTML email.';
            $content = $this->multipart('alternative', [$this->textPart('plain', $text), $this->textPart('html', $this->message)]);
        } else {
            $content = $this->textPart('plain', $this->message);
        }

        if ($this->attachment !== []) {
            $content = $this->multipart('mixed', [$content, ...array_map($this->attachmentPart(...), $this->attachment)]);
        }

        [$contentHeaders, $body] = $content;
        $domain = substr((string) strrchr($this->fromEmail, '@'), 1);
        $headers = [
            'From' => ($this->fromName === '' ? '' : $this->encodeName($this->fromName).' ').'<'.$this->fromEmail.'>',
            'Cc' => implode(', ', $this->rcptCc),
            'Reply-To' => (string) $this->replyTo,
            'Date' => date(DATE_RFC2822),
            'Message-ID' => '<'.bin2hex(random_bytes(16)).'@'.$domain.'>',
            'X-Priority' => (string) $this->priority,
            'X-Mailer' => 'Panada Mailer',
            'MIME-Version' => '1.0',
        ] + $contentHeaders;

        return [array_filter($headers, fn ($value) => $value !== ''), $body];
    }

    /**
     * A text part, quoted-printable: safe for any character and line length.
     *
     * @return array{0: array<string, string>, 1: string}
     */
    private function textPart(string $subtype, string $text): array
    {
        return [
            ['Content-Type' => 'text/'.$subtype.'; charset=utf-8', 'Content-Transfer-Encoding' => 'quoted-printable'],
            quoted_printable_encode(preg_replace('/\r\n|\r|\n/', "\r\n", $text)),
        ];
    }

    /**
     * @return array{0: array<string, string>, 1: string}
     */
    private function attachmentPart(string $file): array
    {
        $name = $this->encodeHeader(str_replace('"', '', basename($file)));

        return [
            [
                'Content-Type' => (Upload::getMimeTypes($file)['type'] ?? 'application/octet-stream').'; name="'.$name.'"',
                'Content-Disposition' => 'attachment; filename="'.$name.'"',
                'Content-Transfer-Encoding' => 'base64',
            ],
            rtrim(chunk_split(base64_encode((string) file_get_contents($file)), 76, "\r\n")),
        ];
    }

    /**
     * @param array<int, array{0: array<string, string>, 1: string}> $parts
     *
     * @return array{0: array<string, string>, 1: string}
     */
    private function multipart(string $subtype, array $parts): array
    {
        $boundary = 'Panada-'.bin2hex(random_bytes(12));
        $body = '';

        foreach ($parts as [$headers, $content]) {
            $body .= '--'.$boundary."\r\n".$this->headerLines($headers)."\r\n\r\n".$content."\r\n";
        }

        return [['Content-Type' => 'multipart/'.$subtype.'; boundary="'.$boundary.'"'], $body.'--'.$boundary.'--'];
    }

    private function headerLines(array $headers): string
    {
        $lines = [];

        foreach ($headers as $name => $value) {
            $lines[] = $name.': '.$value;
        }

        return implode("\r\n", $lines);
    }

    /**
     * A header value on one line; non-ASCII text as an encoded word.
     */
    private function encodeHeader(string $value): string
    {
        $value = trim(str_replace(["\r", "\n"], ' ', $value));

        return mb_check_encoding($value, 'ASCII') ? $value : mb_encode_mimeheader($value, 'UTF-8', 'B', "\r\n");
    }

    private function encodeName(string $name): string
    {
        $name = $this->encodeHeader($name);

        return str_starts_with($name, '=?') ? $name : '"'.addcslashes($name, '"\\').'"';
    }

    private function mailerNative(array $headers, string $body): bool
    {
        if ($this->rcptBcc !== []) {
            $headers['Bcc'] = implode(', ', $this->rcptBcc);
        }

        if (!mail(implode(', ', $this->rcptTo), $this->encodeHeader($this->subject), $body, $headers)) {
            $this->debugMessages[] = 'Error: Sending email failed';

            return false;
        }

        $this->debugMessages[] = 'Success: Sending email succeeded';

        return true;
    }

    private function smtpSend(array $headers, string $body): bool
    {
        $headers = ['To' => implode(', ', $this->rcptTo), 'Subject' => $this->encodeHeader($this->subject)] + $headers;
        $data = $this->headerLines($headers)."\r\n\r\n".$body;

        // A line starting with "." gets a second one, or the server would end
        // the message there (RFC 5321, 4.5.2).
        $data = preg_replace('/^\./m', '..', $data);

        $sent = $this->smtpConnect()
            && $this->smtpEhlo()
            && $this->smtpStartTls()
            && $this->smtpLogin()
            && $this->command('MAIL FROM:<'.$this->fromEmail.'>', [250])
            && $this->smtpRecipients()
            && $this->command('DATA', [354])
            && $this->command($data."\r\n.", [250], '<message>');

        $this->smtpClose();

        return $sent;
    }

    private function smtpConnect(): bool
    {
        $host = ($this->smtpSecure === 'ssl' ? 'ssl://' : '').$this->smtpHost;
        $connection = @fsockopen($host, $this->smtpPort, $errno, $errstr, $this->timeoutConnection);

        if ($connection === false) {
            $this->debugMessages[] = 'Error: Failed to connect to server! Error number: '.$errno.' ('.$errstr.')';

            return false;
        }

        $this->smtpConnection = $connection;
        stream_set_timeout($connection, $this->timeoutConnection);

        return $this->expect([220]);
    }

    private function smtpEhlo(): bool
    {
        return $this->command('EHLO '.$this->smtpEhloHost, [250]) || $this->command('HELO '.$this->smtpEhloHost, [250]);
    }

    /**
     * With smtpSecure 'tls' the connection must be encrypted before the
     * password is sent. When the server refuses, nothing is sent.
     */
    private function smtpStartTls(): bool
    {
        if ($this->smtpSecure !== 'tls') {
            return true;
        }

        if (!$this->command('STARTTLS', [220])) {
            return false;
        }

        if (!@stream_socket_enable_crypto($this->smtpConnection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $this->debugMessages[] = 'Error: TLS handshake failed';

            return false;
        }

        return $this->smtpEhlo();
    }

    private function smtpLogin(): bool
    {
        if ($this->smtpUsername === '') {
            return true;
        }

        return $this->command('AUTH LOGIN', [334])
            && $this->command(base64_encode($this->smtpUsername), [334], '<username>')
            && $this->command(base64_encode($this->smtpPassword), [235], '<password>');
    }

    private function smtpRecipients(): bool
    {
        foreach ([...$this->rcptTo, ...$this->rcptCc, ...$this->rcptBcc] as $recipient) {
            if (!$this->command('RCPT TO:<'.$recipient.'>', [250, 251])) {
                return false;
            }
        }

        return true;
    }

    private function smtpClose(): void
    {
        if ($this->smtpConnection === null) {
            return;
        }

        @fwrite($this->smtpConnection, "QUIT\r\n");
        fclose($this->smtpConnection);
        $this->smtpConnection = null;
    }

    /**
     * Send one command and check the reply code.
     *
     * @param string|null $log what the debug log shows instead of $command
     */
    private function command(string $command, array $codes, ?string $log = null): bool
    {
        fwrite($this->smtpConnection, $command."\r\n");
        $this->debugMessages[] = 'Client: '.($log ?? $command);

        return $this->expect($codes);
    }

    private function expect(array $codes): bool
    {
        $response = '';

        // Multi-line replies use "250-"; the last line has a space: "250 ".
        while (($line = fgets($this->smtpConnection, 515)) !== false) {
            $response .= $line;

            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }

        $ok = in_array((int) substr($response, 0, 3), $codes, true);
        $this->debugMessages[] = ($ok ? 'Success: ' : 'Error: ').trim($response);

        return $ok;
    }
}

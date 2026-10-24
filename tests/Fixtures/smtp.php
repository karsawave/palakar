<?php

// A tiny SMTP server for EmailTest. Serves one session, then prints what the
// client sent (DATA included) to stdout.
//
//   php smtp.php <port> [reject-auth | reject-rcpt]
//
// STARTTLS is always refused: there is no certificate here.

[, $port, $mode] = $argv + [2 => ''];

$server = stream_socket_server('tcp://127.0.0.1:'.$port);
echo "ready\n";

$client = stream_socket_accept($server, 10);
$reply = fn (string $line) => fwrite($client, $line."\r\n");
$transcript = '';
$state = 'command';     // command | username | password | data

$reply('220 <b>fake</b> ESMTP');

while (($line = fgets($client)) !== false) {
    $transcript .= $line;

    if ($state === 'data') {
        if ($line === ".\r\n") {
            $state = 'command';
            $reply('250 queued');
        }
    } elseif ($state === 'username') {
        $state = 'password';
        $reply('334 UGFzc3dvcmQ6');
    } elseif ($state === 'password') {
        $state = 'command';
        $reply('235 ok');
    } else {
        $command = strtoupper(substr(trim($line), 0, 4));

        if ($command === 'EHLO') {
            $reply("250-fake\r\n250-STARTTLS\r\n250 AUTH LOGIN");
        } elseif ($command === 'STAR') {
            $reply('454 TLS not available');
        } elseif ($command === 'AUTH' && $mode === 'reject-auth') {
            $reply('535 bad credentials');
        } elseif ($command === 'AUTH') {
            $state = 'username';
            $reply('334 VXNlcm5hbWU6');
        } elseif ($command === 'RCPT' && $mode === 'reject-rcpt' && str_contains($line, 'bad@')) {
            $reply('550 no such user');
        } elseif ($command === 'DATA') {
            $state = 'data';
            $reply('354 go ahead');
        } elseif ($command === 'QUIT') {
            $reply('221 bye');
            break;
        } else {
            $reply('250 ok');
        }
    }
}

echo $transcript;

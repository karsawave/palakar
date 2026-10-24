<?php
/**
 * Panada Installation Check: open /check.php in the browser.
 *
 * Only works while 'debug' is true in app/config/main.php.
 * Delete this file on production servers.
 *
 * @since	Version 0.4.1
 * @author	Mulia Arifandy Nasution <https://github.com/mul14>
 */

define('INDEX_FILE', 'index.php');
define('APP', dirname(__DIR__).'/app/');
define('VENDOR', dirname(__DIR__).'/vendor/');

$main = require APP.'config/main.php';
$security = require APP.'config/security.php';

if (empty($main['debug'])) {
    http_response_code(404);
    exit;
}

$pdoDrivers = class_exists('PDO') ? PDO::getAvailableDrivers() : [];

// [label, passed, required, note]
$checks = [
    ['PHP 8.2 or newer', version_compare(PHP_VERSION, '8.2.0', '>='), true, 'You have '.PHP_VERSION],
    ['PDO extension', extension_loaded('pdo'), true, 'Database access'],
    ['A PDO driver (mysql, pgsql or sqlite)', (bool) array_intersect(['mysql', 'pgsql', 'sqlite'], $pdoDrivers), true, 'Available: '.(implode(', ', $pdoDrivers) ?: 'none')],
    ['mbstring extension', extension_loaded('mbstring'), true, 'Validation of non-ASCII text'],
    ['sodium extension', extension_loaded('sodium'), false, 'Encryption and the encrypted cookie session'],
    ['app/storage is writable', is_writable(APP.'storage'), false, 'Default SQLite database'],
    ['secretKey changed in app/config/security.php', strlen((string) $security['secretKey']) >= 32 && $security['secretKey'] !== '_put_your_random_string_here_', false, 'Needed by Encryption and the cookie session driver'],
    ['Composer packages installed', is_file(VENDOR.'autoload.php'), false, 'Optional. Run `composer install`'],
    ['Whoops error page', is_dir(VENDOR.'filp/whoops'), false, 'Optional, development only'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panada Installation Check</title>
    <link rel="stylesheet" href="assets/css/main.css" type="text/css" media="screen" />
    <style>
    table { width: 100%; border-collapse: collapse; }
    td { padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 13px; }
    .pass { color: #191; font-weight: bold; }
    .fail { color: #911; font-weight: bold; }
    .warn { color: #a60; font-weight: bold; }
    </style>
</head>
<body>
<div id="konten">
    <h1>Panada Installation Check</h1>
    <table>
    <?php foreach ($checks as [$label, $passed, $required, $note]): ?>
        <tr>
            <td><?= htmlspecialchars($label) ?></td>
            <td class="<?= $passed ? 'pass' : ($required ? 'fail' : 'warn') ?>"><?= $passed ? 'OK' : ($required ? 'Required' : 'Recommended') ?></td>
            <td><?= htmlspecialchars($note) ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
    <p>Delete <code>public/check.php</code> before going to production.</p>
</div>
</body>
</html>

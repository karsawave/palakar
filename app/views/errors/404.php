<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Error <?= e($status ?? 404) ?> - Page Not Found!</title>
<?php /* Standalone on purpose (no layout); inline style is the fallback when the URI is not known yet. */ ?>
<style>body{font:1rem/1.6 system-ui,sans-serif;max-width:48rem;margin:auto;padding:0 1rem}</style>
<?php if ($this->uri): ?><link rel="stylesheet" href="<?= e($this->uri->baseUri) ?>assets/css/main.css"><?php endif; ?>
</head>
<body>
<main>
    <h1><?= ($status ?? 404) === 404 ? 'Page not found!' : 'Error ' . e($status) ?></h1>
    <?php if (!empty($debug)): ?>
    <p>Message: <code><?= e($message) ?></code></p>
    <?php else: ?>
    <p>The requested URL <code><?= e($_SERVER['REQUEST_URI'] ?? '/') ?></code> was not found on this server.</p>
    <?php endif; ?>
</main>
</body>
</html>

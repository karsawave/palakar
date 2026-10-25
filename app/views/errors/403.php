<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Error 403 - Forbidden</title>
<?php /* Standalone on purpose (no layout); inline style is the fallback when the URI is not known yet. */ ?>
<style>body{font:1rem/1.6 system-ui,sans-serif;max-width:48rem;margin:auto;padding:0 1rem}</style>
<?php if ($this->uri): ?><link rel="stylesheet" href="<?= e($this->uri->baseUri) ?>assets/css/main.css"><?php endif; ?>
</head>
<body>
<main>
    <h1>Forbidden</h1>
    <p><?= e($message ?: 'You are not allowed to do this.') ?></p>
    <p>If you were submitting a form, go back, reload the page and try again.</p>
</main>
</body>
</html>

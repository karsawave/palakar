<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Error 500 - Internal Server Error!</title>
<?php /* Standalone on purpose (no layout); inline style is the fallback when the URI is not known yet. */ ?>
<style>body{font:1rem/1.6 system-ui,sans-serif;max-width:48rem;margin:auto;padding:0 1rem}pre{overflow:auto}.current{font-weight:700}</style>
<?php if ($this->uri): ?><link rel="stylesheet" href="<?= e($this->uri->baseUri) ?>assets/css/main.css"><?php endif; ?>
</head>
<body>
<main>
<h1><?= !empty($debug) ? 'Runtime Error!' : 'Something went wrong' ?></h1>
<p><strong>Error message</strong>: <?= e($message) ?></p>
<?php if (!empty($debug)): ?>
<p><strong><?= e($class) ?></strong> in file:</p>
<pre><?= e($file) ?> line <?= e($line) ?></pre>
<?php if ($code): ?>
<h2>Source code</h2>
<pre><?php foreach ($code as $row): ?><span class="<?= $row['current'] ? 'current' : '' ?>"><span class="n"><?= $row['number'] ?></span><?= e($row['text']) ?></span>
<?php endforeach; ?></pre>
<?php endif; ?>
<h2>Debug trace</h2>
<pre><?= e($trace) ?></pre>
<?php endif; ?>
</main>
</body>
</html>

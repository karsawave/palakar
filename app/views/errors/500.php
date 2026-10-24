<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Error 500 - Internal Server Error!</title>
<style>
html{background:#ddd;}
body{background:#fff;color:#333;font-family:"Lucida Grande",Verdana,Arial,sans-serif;margin:1em auto;max-width:1000px;padding:1em 2em;border-radius:11px;border:1px solid #999;}
h1{border-bottom:1px solid #dadada;color:#666;font:24px Georgia,"Times New Roman",Times,serif;margin:5px 0 0 -4px;padding-top:5px;}
p{font-size:13px;line-height:18px;}
pre{font:13px Consolas,"Courier New",monospace;background:#ddf;border:1px solid #d0d0d0;margin:14px 0;padding:10px;border-radius:8px;overflow:auto;white-space:pre;}
.n{display:inline-block;width:3em;color:#888;}
.current{background:#fcc;color:#d00;display:block;}
</style>
</head>
<body>
<h1><?= !empty($debug) ? 'Runtime Error!' : 'Something went wrong' ?></h1>
<p><strong>Error message</strong>: <?= e($message) ?></p>
<?php if (!empty($debug)): ?>
<p><strong><?= e($class) ?></strong> in file:</p>
<pre><?= e($file) ?> line <?= e($line) ?></pre>
<?php if ($code): ?>
<p><strong>Source code</strong></p>
<pre><?php foreach ($code as $row): ?><span class="<?= $row['current'] ? 'current' : '' ?>"><span class="n"><?= $row['number'] ?></span><?= e($row['text']) ?></span>
<?php endforeach; ?></pre>
<?php endif; ?>
<p><strong>Debug trace</strong></p>
<pre><?= e($trace) ?></pre>
<?php endif; ?>
</body>
</html>

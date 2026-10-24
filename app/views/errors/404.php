<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Error <?= e($status ?? 404) ?> - Page Not Found!</title>
<style>
html{background:#ddd;}
body{background:#fff;color:#333;font-family:"Lucida Grande",Verdana,Arial,sans-serif;margin:1em auto;max-width:900px;padding:1em 2em;border-radius:11px;border:1px solid #999;}
h1{border-bottom:1px solid #dadada;color:#666;font:24px Georgia,"Times New Roman",Times,serif;margin:5px 0 0 -4px;padding-top:5px;}
p{font-size:13px;line-height:18px;}
code{font:13px Consolas,"Courier New",monospace;background:#ddf;color:#c00;padding:2px 4px;border-radius:4px;}
</style>
</head>
<body>
    <h1><?= ($status ?? 404) === 404 ? 'Page not found!' : 'Error '.e($status) ?></h1>
    <?php if (!empty($debug)): ?>
    <p>Message: <code><?= e($message) ?></code></p>
    <?php else: ?>
    <p>The requested URL <code><?= e($_SERVER['REQUEST_URI'] ?? '/') ?></code> was not found on this server.</p>
    <?php endif; ?>
</body>
</html>

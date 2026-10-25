<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Palakar') ?></title>
    <link rel="stylesheet" href="<?= e($this->uri->baseUri) ?>assets/css/main.css">
    <?= $head ?? '' ?>
</head>
<body>
<a class="lewati" href="#isi">Skip to content</a>
<header class="situs">
    <a class="logo" href="<?= e($this->location()) ?>">Palakar</a>
</header>
<main id="isi">
    <?php /* Already-rendered HTML from a view that escaped its own data: not e(). */ ?>
    <?= $content ?>
</main>
<?= $this->output('partials/footer') ?>
<?= $scripts ?? '' ?>
</body>
</html>

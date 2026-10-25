<?php
$groups = array_values(array_unique(array_column($files, 'group')));
$maxLines = max(array_column($files, 'lines'));
$totalLines = array_sum(array_column($files, 'lines'));
$source = 'https://github.com/karsawave/palakar/blob/master/panada/';
?>
<section class="sleeve" aria-labelledby="sleeve-title">
    <div class="sleeve-text">
        <h1 id="sleeve-title">Palakar <?= e(Gear::VERSION) ?></h1>
        <p>A simple and secure PHP framework. Every line in this picture is one file of its core:
            <?= count($files) ?> files, <?= e(number_format($totalLines)) ?> lines, small enough to read in one evening.</p>
        <a class="item primary" href="<?= e($this->location('guestbook')) ?>"><span class="no">PLK 01</span> Try the guestbook demo</a>
    </div>

    <figure class="plot">
        <svg viewBox="0 0 640 <?= 52 + count($files) * 9 ?>" role="img" aria-labelledby="plot-caption">
            <?php foreach ($files as $i => $file):
                $y = 44 + $i * 9;
                $width = 560 * $file['lines'] / $maxLines;
                $x0 = 320 - $width / 2;
                $profile = $file['profile'];
                $points = '40,' . $y . ' ';
                foreach ($profile as $j => $indent) {
                    // Three-point average: the shape of the code, not every single line.
                    $indent = ($profile[max($j - 1, 0)] + $indent + $profile[min($j + 1, count($profile) - 1)]) / 3;
                    $points .= round($x0 + $width * $j / (count($profile) - 1), 1) . ',' . round($y - min($indent, 16) / 16 * 36, 1) . ' ';
                }
                $points .= '600,' . $y;
                ?>
            <g class="f c<?= array_search($file['group'], $groups, true) ?>">
                <title><?= e($file['path']) ?>, <?= e($file['lines']) ?> lines</title>
                <polygon points="<?= $points ?> 600,<?= $y + 9 ?> 40,<?= $y + 9 ?>"/>
                <polyline points="<?= $points ?>"/>
            </g>
            <?php endforeach; ?>
        </svg>
        <figcaption id="plot-caption">One line per file of <code>panada/</code>; its shape is the indentation of the code.</figcaption>
    </figure>
</section>

<section aria-labelledby="index-title">
    <h2 id="index-title">Index</h2>
    <ol class="index">
        <li><a class="item" href="<?= e($this->location('guestbook')) ?>"><span class="no">PLK 01</span> Try the guestbook demo <small>CSRF protection, validation and PDO prepared statements</small></a></li>
        <li><div class="item"><span class="no">PLK 02</span> Edit this page <small><code>app/views/home.php</code> and <code>app/Controllers/Home.php</code></small></div></li>
        <li><div class="item"><span class="no">PLK 03</span> Change the look <small><code>app/views/layouts/main.php</code> and <code>public/assets/css/main.css</code></small></div></li>
        <li><a class="item" href="check.php"><span class="no">PLK 04</span> Check your server <small>PHP version and extensions this app needs</small></a></li>
        <li><a class="item" href="https://github.com/karsawave/palakar#readme"><span class="no">PLK 05</span> Read the documentation <small>Routing, layouts, modules, upgrading from Panada 1.x</small></a></li>
    </ol>
</section>

<section aria-labelledby="catalog-title">
    <h2 id="catalog-title">The core, file by file</h2>
    <ul class="legend">
        <?php foreach ($groups as $n => $group): ?>
        <li class="c<?= $n ?>"><?= e($group) ?></li>
        <?php endforeach; ?>
    </ul>
    <ol class="catalog">
        <?php foreach ($files as $file): ?>
        <li class="c<?= array_search($file['group'], $groups, true) ?>"><a href="<?= e($source . $file['path']) ?>"><?= e(basename($file['path'])) ?></a> <span><?= e($file['lines']) ?></span></li>
        <?php endforeach; ?>
    </ol>
</section>

<?php if ($debug): ?>
<p class="note">Debug mode is on. Application folder: <code><?= e(APP) ?></code>. Set <code>'debug' =&gt; false</code> in <code>app/config/main.php</code> before going live.</p>
<?php endif; ?>

<?=$this->output('header');?>
<body>
<h1 class="logo">Palakar</h1>
<div id="konten">
    <h1>Palakar has been installed successfully!</h1>
    <p>This is sample page view. You find this file at:</p>
    <code><?= e(APP) ?>views/home.php</code>
    <p>The controller of this page is located it:</p>
    <code><?= e(APP) ?>Controllers/Home.php</code>
    <p>Base URL for this application is:</p>
    <code><?= e($this->uri->baseUri) ?></code>

    <h1>Documentation and resource</h1>
    <ul>
        <li>To check your system minimum requirements, please click this <a href="check.php">Checking Tools</a>.</li>
        <li>To try a form with CSRF protection, validation and PDO, open the <a href="<?= e($this->location('guestbook')) ?>">guestbook demo</a>.</li>
        <li>To see Hello World page from a module sample, <a href="<?= e($this->location('modSample')) ?>">click here.</a></li>
        <li>Source code, contributing and bug reports: <a target="_blank" rel="noopener" href="https://github.com/karsawave/palakar">github.com/karsawave/palakar</a>.</li>
    </ul>
    <p>
        Palakar is a fork of <a target="_blank" rel="noopener" href="https://github.com/panada/Panada">Panada Framework</a> 1.1.1
        by Iskandar Soesman, licensed under the <a href="https://github.com/karsawave/palakar/blob/main/LICENSE">BSD License</a>.
    </p>
</div>

<div id="foot">Powered by <a href="https://github.com/karsawave/palakar">Palakar</a> version 0.1.0</div>
</body>
</html>

<h1>Palakar has been installed successfully!</h1>
<p>This is a sample page view. You find this file at:</p>
<code><?= e(APP) ?>views/home.php</code>
<p>It is wrapped by the layout at:</p>
<code><?= e(APP) ?>views/layouts/main.php</code>
<p>The controller of this page is located at:</p>
<code><?= e(APP) ?>Controllers/Home.php</code>
<p>Base URL for this application is:</p>
<code><?= e($this->uri->baseUri) ?></code>

<h2>Documentation and resources</h2>
<ul>
    <li>To check your system minimum requirements, open the <a href="check.php">checking tool</a>.</li>
    <li>To try a form with CSRF protection, validation and PDO, open the <a href="<?= e($this->location('guestbook')) ?>">guestbook demo</a>.</li>
    <li>To see a Hello World page from a module sample, open <a href="<?= e($this->location('modSample')) ?>">the module sample</a>.</li>
    <li>Source code, contributing and bug reports: <a href="https://github.com/karsawave/palakar">github.com/karsawave/palakar</a>.</li>
</ul>

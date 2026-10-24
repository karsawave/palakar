<?= $this->output('header') ?>
<body>
<div id="konten">
    <h1>Guestbook</h1>

    <?php if ($errors): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <form method="post" action="<?= e($this->location('guestbook')) ?>">
        <?= csrf_field() ?>
        <p><label>Name<br><input name="name" value="<?= e($old['name'] ?? '') ?>"></label></p>
        <p><label>Email<br><input name="email" type="email" value="<?= e($old['email'] ?? '') ?>"></label></p>
        <p><label>Message<br><textarea name="message" rows="4"><?= e($old['message'] ?? '') ?></textarea></label></p>
        <p><button type="submit">Send</button></p>
    </form>

    <?php foreach ($entries as $entry): ?>
    <div class="entry">
        <strong><?= e($entry->name) ?></strong> <small><?= e($entry->created_at) ?></small>
        <p><?= nl2br(e($entry->message)) ?></p>
    </div>
    <?php endforeach; ?>
</div>
</body>
</html>

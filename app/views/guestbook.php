<h1>Guestbook</h1>

<?php if ($errors): ?>
<ul class="errors" role="alert">
    <?php foreach ($errors as $error): ?>
    <li><?= e($error) ?></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<form method="post" action="<?= e($this->location('guestbook')) ?>">
    <?= csrf_field() ?>
    <p><label for="name">Name</label><input id="name" name="name" autocomplete="name" value="<?= e($old['name'] ?? '') ?>"></p>
    <p><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" value="<?= e($old['email'] ?? '') ?>"></p>
    <p><label for="message">Message</label><textarea id="message" name="message" rows="4"><?= e($old['message'] ?? '') ?></textarea></p>
    <p><button type="submit">Send</button></p>
</form>

<?php foreach ($entries as $entry): ?>
<article class="entry">
    <strong><?= e($entry->name) ?></strong> <small><?= e($entry->created_at) ?></small>
    <p><?= nl2br(e($entry->message)) ?></p>
</article>
<?php endforeach; ?>

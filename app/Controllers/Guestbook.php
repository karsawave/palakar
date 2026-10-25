<?php

namespace Controllers;

use Resources;

/**
 * Small demo: CSRF-protected form, validation with sanitizing,
 * PDO prepared statements and escaped output. Open /guestbook.
 */
class Guestbook extends Resources\Controller
{
    public function index(): string
    {
        $guestbook = $this->model->guestbook();
        $form = $this->library->guestbookForm();

        // The CSRF token was already checked by Gear before we got here.
        if (Resources\Request::method() === 'POST' && $form->validate()) {
            $guestbook->add($form->value());

            return $this->redirect('guestbook');
        }

        return $this->output('layouts/main', [
            'title' => 'Guestbook',
            'content' => $this->output('guestbook', [
                'entries' => $guestbook->latest(),
                'errors' => $form->errorMessages() ?? [],
                'old' => $_POST,
            ]),
        ]);
    }
}

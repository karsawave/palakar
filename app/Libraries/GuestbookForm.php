<?php

namespace Libraries;

use Resources\Validation;

class GuestbookForm extends Validation
{
    public function setRules(): array
    {
        return [
            'name' => [
                'label' => 'Name',
                'rules' => ['required', 'max' => 50],
                'filter' => ['strip_tags'],
            ],
            'email' => [
                'label' => 'Email',
                'rules' => ['required', 'email'],
                'filter' => ['lower'],
            ],
            'message' => [
                'label' => 'Message',
                'rules' => ['required', 'min' => 3, 'max' => 500],
            ],
        ];
    }
}

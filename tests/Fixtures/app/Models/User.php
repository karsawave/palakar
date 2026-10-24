<?php

namespace Models;

class User extends \Resources\ActiveRecord
{
    protected $table = 'users';
    protected $connection = 'ar';

    public function relations()
    {
        return [
            'posts' => [self::HAS_MANY, 'post', 'user_id'],
            'profile' => [self::HAS_ONE, 'profile', 'user_id'],
        ];
    }
}

<?php

namespace Models;

class Post extends \Resources\ActiveRecord
{
    protected $connection = 'ar';

    public function relations()
    {
        return [
            'user' => [self::BELONGS_TO, 'user', 'user_id'],
            'tags' => [self::MANY_MANY, 'tag', ['post_tag', 'tag_id', 'post_id']],
        ];
    }
}

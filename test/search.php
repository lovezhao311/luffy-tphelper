<?php

class Test extends Search
{
    protected $rules = [
        'name' => [
            'default' => null,
            'field' => 'a.name|a.phone',
            'exp' => 'like',
        ],
        'status' => [
            'field' => 'a.name|a.phone',
        ],
    ];

}

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model Class
    |--------------------------------------------------------------------------
    |
    | User class helps the application to work with user data
    |
    */
    'user' => null,

    /*
    |--------------------------------------------------------------------------
    | Lock Service Configs
    |--------------------------------------------------------------------------
    |
    | Lock service helps you to manage locking system, like joining to channels
    |
    */
    'lock' => [
        'groups' => [
            'main' => 'panelkit::lock.groups.main',
        ],
        'condition' => null,
        'fixed' => [
            // [
            //     'chat_id' => -123455678,
            //     'title' => 'Join',
            //     'url' => 'https://t.me/Link',
            //     'group' => 'main',
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Back Actions
    |--------------------------------------------------------------------------
    |
    | When a user or admin clicked on "Back" button, what methods should
    | be run?
    |
    */
    'back' => [
        'admin'  => null, // [class, method],
    ],
];

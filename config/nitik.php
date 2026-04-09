<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Log Levels
    |--------------------------------------------------------------------------
    |
    | The log levels that should be captured and stored in the database.
    |
    */
    'log_levels' => [
        'error',
        'critical',
        'emergency',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore Exceptions
    |--------------------------------------------------------------------------
    |
    | List of exception classes that should be ignored by the Nitik log driver.
    |
    */
    'ignore_exceptions' => [
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix used for the package tables.
    |
    */
    'table_prefix' => 'nitik_',
];

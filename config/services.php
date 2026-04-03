<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

        //REGISTRAMOS LA API DE BREVO EN ESTE ARCHIVO PARA QUE EL SERVICIO DE ENVIO DE CORREOS SEA POSIBLE Y ASIGNAMOS VARIABLES PARA QUE ESTAS SE CONECTEN CON LAS QUE ESTAN EN EL ARCHIVO .env 
    'brevo' => [
    'key'       => env('BREVO_API_KEY'),
    'from_email'=> env('BREVO_FROM_EMAIL'),
    'from_name' => env('BREVO_FROM_NAME'),
    ]

];
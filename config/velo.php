<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Initial Administrator
    |--------------------------------------------------------------------------
    |
    | Credentials used by the database seeder to create the first admin
    | account. Change the password immediately after the first login.
    |
    */

    'admin' => [
        'name' => env('VELO_ADMIN_NAME', 'Velo Admin'),
        'email' => env('VELO_ADMIN_EMAIL', 'admin@velo.test'),
        'password' => env('VELO_ADMIN_PASSWORD', 'password'),
    ],

];

<?php
// Copy this file to config.php and put your real values there.
// config.php is git-ignored; only this example file is committed.

return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'redline_autos',
        'user'    => 'your_db_user',
        'pass'    => 'your_db_password',
        'charset' => 'utf8mb4',
    ],


    'external_api' => [
        'vin_decoder_url' => 'https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/',
        'timeout'         => 5,   // seconds for the whole request
        'connect_timeout' => 3,   // seconds to connect
    ],
];

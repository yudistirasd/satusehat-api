<?php

return [

    'enable' => env('SATUSEHAT_ENABLE', true),
    'env' => env('SATUSEHAT_ENV', 'DEV'),
    'client_id_DEV' => env('CLIENTID_DEV'),
    'client_secret_DEV' => env('CLIENTSECRET_DEV'),
    'organization_id_DEV' => env('ORGID_DEV'),
    'cache_driver' => env('SATUSEHAT_CACHE_DRIVER', 'file'),

    /*
     * This is the name of the table that will be created by the migration and
     * used by the Activity model shipped with this package.
     */
    'log_table_name' => 'satusehat_log',
    'token_table_name' => 'satusehat_token',

    'icd10_table_name' => 'satusehat_icd10',
    'icd9_table_name' => 'satusehat_icd9',

    'kode_wilayah_indonesia_table_name' => 'kode_wilayah_indonesia',
    'wilayah' => [
        'provinsi_table_name' => 'satusehat_provinsi',
        'kabupaten_table_name' => 'satusehat_kabupaten',
        'kecamatan_table_name' => 'satusehat_kecamatan',
        'kelurahan_table_name' => 'satusehat_kelurahan',
    ],
    'profile_fasyankes_name' => 'satusehat_profile_fasyankes',

    /*
     * Override the SATUSEHAT environment, organization, ClientID, and ClientSecret to use
     * non environment variable
     */

    'ss_parameter_override' => false,

    /*
     * This is the database connection that will be used by the migration and
     * the Activity model shipped with this following Laravel's database.default
     * If not set, it will use mysql instead.
     */
    'database_connection_master' => env('DB_CONNECTION_MASTER', 'mysql'),
    'database_connection_satusehat' => env('DB_CONNECTION', 'mysql'),
];

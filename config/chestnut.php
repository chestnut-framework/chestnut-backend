<?php

return [
    /**
     * Chestnut Auth config
     */
    'auth'      => [

        /**
         * Api token expire time.
         */
        'expire' => 60 * 60 * 24 * 7,

        /**
         * Enable Role-base access control
         */
        'rbac'   => true,
    ],

    /**
     * Chestnut Dashboard config
     */
    'dashboard' => [

        /**
         * Nuts directory
         */
        'nutsIn'         => app_path('Nuts'),

        /**
         * Nut ORM Drivers
         */
        'drivers'        => [
            'eloquent' => Chestnut\Dashboard\ORMDriver\EloquentDriver::class,
        ],

        /**
         * Nut ORM default driver
         */
        'driver'         => 'eloquent',

        'upload_storage' => 'upload',
    ],
];

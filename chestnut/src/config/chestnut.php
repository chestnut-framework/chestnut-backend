<?php

return [
    /**
     * Chestnut Auth config
     */
    'auth' => [

        /**
         * Api token expire time.
         */
        'expire' => 60 * 60 * 24 * 7,

        /**
         * Enable Role-base access control
         */
        'rbac' => false,
    ],

    'dashboard' => [
        'nutsIn' => app_path('Nuts'),
    ],
];

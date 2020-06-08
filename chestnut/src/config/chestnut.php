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
    ],

    'dashboard' => [
        'nutsIn' => app_path('Nuts'),
    ],
];

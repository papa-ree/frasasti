<?php

use Bale\Frasasti\FrasastiPermissions;

/**
 * Menu definisi untuk package bale/frasasti (Landlord Layout).
 *
 * Grup 'aduan-masuk' berisi fitur pengelolaan aduan masuk (incoming complaints).
 */
return [
    'type' => 'landlord',

    'groups' => [
        [
            'key' => 'aduan-masuk',
            'label' => 'Frasasti',
            'icon' => 'headset',
            'items' => [
                [
                    'label' => 'Aduan Masuk',
                    'url' => 'frasasti/aduan-masuk',
                    'icon' => 'inbox',
                    'permission' => FrasastiPermissions::READ_ADUAN,
                    'table' => 'frasasti_aduans',
                ],
            ],
        ],
    ],
];

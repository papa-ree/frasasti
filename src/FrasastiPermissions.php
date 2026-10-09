<?php

namespace Bale\Frasasti;

/**
 * Permission constants untuk package bale/frasasti.
 */
class FrasastiPermissions
{
    const READ_ADUAN = 'aduan.read';

    const UPDATE_ADUAN = 'aduan.update';

    const ALL = [
        self::READ_ADUAN,
        self::UPDATE_ADUAN,
    ];
}

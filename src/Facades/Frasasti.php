<?php

namespace Bale\Frasasti\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bale\Frasasti\Frasasti
 */
class Frasasti extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Bale\Frasasti\Frasasti::class;
    }
}

<?php

namespace Deal\Modules\Qsapi\Parser;

/**
 * An interface for parser factories. AbstractFactory pattern
 */
interface FactoryInterface
{
    public static function create(array $options = array());
}

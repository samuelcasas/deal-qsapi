<?php

namespace Deal\Tests\Modules\Qsapi;

use \PHPUnit_Framework_TestCase as TestCase;

/**
 * Base test
 */
abstract class BaseTest extends TestCase
{
    protected function performDrilldownAssertionsAndReturn($arr, $path, $sep = '.')
    {
        $this->assertInternalType('array', $arr);
        if (is_string($path)) {
            $path = explode($sep, $path);
        }
        $ptr = $arr;
        foreach ($path as $s) {
            $this->assertArrayHasKey($s, $ptr);
            $ptr = $ptr[$s];
        }
        return $ptr;
    }    
}

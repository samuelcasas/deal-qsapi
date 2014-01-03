<?php

namespace Deal\Tests\Modules\Qsapi\Util\String;

use Deal\Modules\Qsapi\Util\String as StringUtil;

/**
 * Tests for the String util class
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
    
    public function dpTestIsInteger()
    {
        return array(
            array('0', true),
            array('+0', true),
            array('-0', true),
            array('-5', true),
            array('5', true),
            array('+-5', false),
            array('abc', false),
            array('"', false),
            array(':', false),
            array('1.5', false),
            array('-1.5', false),
            array('1/2', false),
        );
    }
    
    /**
     * @dataProvider dpTestIsInteger
     * @param string $value
     * @param boolean $expectedResult
     */
    public function testIsInteger($value, $expectedResult)
    {
        $assertion = 'assert' . ($expectedResult ? 'True' : 'False');
        $this->$assertion(StringUtil::isInteger($value));
    }
}

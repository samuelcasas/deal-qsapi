<?php

namespace Deal\Tests\Modules\Qsapi\Engine;

use Deal\Modules\Qsapi\Engine;

/**
 * Test for the PhpStrParse engine
 */
class PhpStrParseTest extends \PHPUnit_Framework_TestCase
{
   public function testResultIsSameAsPhpParseStrFunction()
    {
        $engine = new Engine\PhpParseStr();
        
        $qs = 'k1=v1&k2=v2';
        parse_str($qs, $expectedResult);
        $this->assertEquals($expectedResult, $engine->parse($qs));
    }
}

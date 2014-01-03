<?php

namespace Deal\Tests\Modules\Qsapi\Engine;

use Deal\Modules\Qsapi\Engine;

/**
 * Test for the Fixed engine
 */
class FixedTest extends \PHPUnit_Framework_TestCase
{
   public function testResultIsFixed()
    {
       $fixedResult = array(
            'k1' => 'v1',
            'k2' => 'v2',
        ); 
        $engine = new Engine\Fixed($fixedResult); 
        $this->assertEquals($fixedResult, $engine->parse('xxx=yyy'));
    }
}

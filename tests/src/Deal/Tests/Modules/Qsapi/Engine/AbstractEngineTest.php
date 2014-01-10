<?php

namespace Deal\Tests\Modules\Qsapi\Engine;

use Deal\Tests\Modules\Qsapi\BaseTest;

/**
 * Test for the AbstractEngine
 */
class AbstractEngineTest extends BaseTest
{
   
   protected function getMockedEngine()
   {
        $engine = $this->getMockForAbstractClass('Deal\Modules\Qsapi\Engine\AbstractEngine');
        $engine->expects($this->once())
                ->method('parseFilter')
                ->withAnyParameters()
                ->will($this->returnValue(array()));
        return $engine;
   }
   
   public function testFieldsNonEmptyReturnsCorrectArray()
   {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('fields=f1,f2,f3');
       $val = $this->performDrilldownAssertionsAndReturn($result, 'fields');
       $this->assertEquals(array('f1', 'f2', 'f3'), $val);
    }
    
    public function testFieldsEmptyReturnNull()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('');
       $val = $this->performDrilldownAssertionsAndReturn($result, 'fields');
       $this->assertNull($val);        
    }

    /**
     * @expectedException Deal\Modules\Qsapi\Engine\EngineException
     */
    public function testFieldsNotAStringThrowsException()
    {
       $engine = $this->getMockedEngine();
       $engine->parse('fields[]=abc');
    }
    
   public function testExcludeNonEmptyReturnsCorrectArray()
   {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('exclude=f1,f2,f3');    
       $val = $this->performDrilldownAssertionsAndReturn($result, 'exclude');
       $this->assertEquals(array('f1', 'f2', 'f3'), $val);
    }
    
    public function testExcludeEmptyReturnsNull()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('');
       $val = $this->performDrilldownAssertionsAndReturn($result, 'exclude');
       $this->assertNull($val);
    }

    /**
     * @expectedException Deal\Modules\Qsapi\Engine\EngineException
     */
    public function testExcludeNotAStringThrowsException()
    {
       $engine = $this->getMockedEngine();
       $engine->parse('exclude[]=abc');
    }
    
    public function testOrderEmptyReturnsNull()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('');
       $val = $this->performDrilldownAssertionsAndReturn($result, 'order');
       $this->assertNull($val);
    }
    
    public function dpTestOrderExceptions()
    {
        return array(
            
            // should be string, not array
            array('order[]=somefield'),
            
            // missing direction
            array('order=somefield'),

            // bad direction delimiter
            array('order=somefield~asc'),

            // bad order
            array('order=somefield:rising'),

            // bad order
            array('order=somefield:falling'),
        );
    }
    /**
     * @dataProvider dpTestOrderExceptions
     * @expectedException Deal\Modules\Qsapi\Engine\EngineException
     */
    public function testOrderExceptions($qs)
    {
       $engine = $this->getMockedEngine();
       $engine->parse($qs);        
    }
    
    public function dpTestOrderWithGoodDirection()
    {
        return array(
            array('a'),
            array('asc'),
            array('ascending'),
            array('d'),
            array('desc'),
            array('descending'),
        );
    }
    
    /**
     * @dataProvider dpTestOrderWithGoodDirection
     */
    public function testOrderWithGoodDirection($dir)
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('order=myfield:' . $dir);
       $val = $this->performDrilldownAssertionsAndReturn($result, 'order');
       $this->assertArrayHasKey('myfield', $val);
    }
    
    public function testCompoundOrder()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('order=myfield1:asc,myfield2:desc');       
       $val = $this->performDrilldownAssertionsAndReturn($result, 'order');
       $this->assertEquals(array(
           'myfield1' => 'asc',
           'myfield2' => 'desc',
       ), $val);
    }
    
    public function dpTestCanonicalDirection()
    {
        return array(
            array('a', 'asc'),
            array('asc', 'asc'),
            array('ascending', 'asc'),
            array('d', 'desc'),
            array('desc', 'desc'),
            array('descending', 'desc'),
        );
    }
    
    /**
     * @dataProvider dpTestCanonicalDirection
     * @param string $qsDir
     * @param string $expectedDir
     */
    public function testCanonicalDirections($qsDir, $expectedDir)
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('order=myfield:' . $qsDir);
       $val = $this->performDrilldownAssertionsAndReturn($result, 'order.myfield');
       $this->assertEquals($expectedDir, $val);
    }
    
    public function dpTestPageWithInvalidValueThrowsException()
    {
        return array(
            array('page[]=1'),
            array('page[xxx]=1'),
            array('page=0'),
            array('page=-1'),
        );
    }
    
    /**
     * @dataProvider dpTestPageWithInvalidValueThrowsException
     * @expectedException Deal\Modules\Qsapi\Engine\EngineException
     */
    public function testPageWithInvalidValueThrowsException($qs)
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse($qs); 
    }
    
    public function dtTestPageWithValidValue()
    {
        return array(
            array(1),
            array(2),
            array(15),
            array(300),
        );
    }
    
    /**
     * @dataProvider dtTestPageWithValidValue
     * @param integer $page
     */
    public function testPageWithValidValue($page)
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('page=' . $page);
       $val = $this->performDrilldownAssertionsAndReturn($result, 'page');
       $this->assertEquals($page, $val);
    }

    public function testEmptyPageReturnsNull()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse();
       $val = $this->performDrilldownAssertionsAndReturn($result, 'page');
       $this->assertNull($val);
    }

    public function dpTestLimitWithInvalidValueThrowsException()
    {
        return array(
            array('limit[]=1'),
            array('limit[xxx]=1'),
            array('limit=0'),
            array('limit=-1'),
        );
    }
    
    /**
     * @dataProvider dpTestLimitWithInvalidValueThrowsException
     * @expectedException Deal\Modules\Qsapi\Engine\EngineException
     */
    public function testLimitWithInvalidValueThrowsException($qs)
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse($qs); 
    }
    
    public function dtTestLimitWithValidValue()
    {
        return array(
            array(1),
            array(2),
            array(15),
            array(300),
        );
    }
    
    /**
     * @dataProvider dtTestLimitWithValidValue
     * @param integer $limit
     */
    public function testLimitWithValidValue($limit)
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('limit=' . $limit);
       $val = $this->performDrilldownAssertionsAndReturn($result, 'limit');
       $this->assertEquals($limit, $val);
    }
    
    public function testEmptyLimitReturnsNull()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse();
       $val = $this->performDrilldownAssertionsAndReturn($result, 'limit');
       $this->assertNull($val);
    }
    
    public function testDistinctCountValid()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('count_distinct=myfield');
       $val = $this->performDrilldownAssertionsAndReturn($result, 'count_distinct');
       $this->assertEquals('myfield', $val);
    }
    
    public function testDistinctCountMissingReturnsFalse()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('some_irrelevant_thing=myfield');
       $val = $this->performDrilldownAssertionsAndReturn($result, 'count_distinct');
       $this->assertFalse($val);
    }
}

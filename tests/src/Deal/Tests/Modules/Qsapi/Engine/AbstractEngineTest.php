<?php

namespace Deal\Tests\Modules\Qsapi\Engine;

/**
 * Test for the AbstractEngine
 */
class AbstractEngineTest extends \PHPUnit_Framework_TestCase
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
      
       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('fields', $result);
       $this->assertEquals(array('f1', 'f2', 'f3'), $result['fields']);
    }
    
    public function testFieldsEmptyReturnNull()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('');
       
       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('fields', $result);
       $this->assertNull($result['fields']);        
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
      
       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('exclude', $result);
       $this->assertEquals(array('f1', 'f2', 'f3'), $result['exclude']);
    }
    
    public function testExcludeEmptyReturnsNull()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('');
       
       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('exclude', $result);
       $this->assertNull($result['exclude']);        
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
       
       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('order', $result);
       $this->assertNull($result['order']);
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
       
       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('order', $result);
       $this->assertArrayHasKey('myfield', $result['order']);        
    }
    
    public function testCompoundOrder()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('order=myfield1:asc,myfield2:desc');
       
       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('order', $result);
       $this->assertEquals(array(
           'myfield1' => 'asc',
           'myfield2' => 'desc',
       ), $result['order']);
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
       
       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('order', $result);
       $this->assertArrayHasKey('myfield', $result['order']);
       $this->assertEquals($expectedDir, $result['order']['myfield']);
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

       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('page', $result);
       $this->assertEquals($page, $result['page']);
    }

    public function testEmptyPageReturnsNull()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse();

       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('page', $result);
       $this->assertNull($result['page']);        
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

       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('limit', $result);
       $this->assertEquals($limit, $result['limit']);
    }
    
    public function testEmptyLimitReturnsNull()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse();

       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('limit', $result);
       $this->assertNull($result['limit']);        
    }
    
    public function testDistinctCountValid()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('count_distinct=myfield');

       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('count_distinct', $result);
       $this->assertEquals('myfield', $result['count_distinct']);
    }
    
    public function testDistinctCountMissingReturnsFalse()
    {
       $engine = $this->getMockedEngine();
       $result = $engine->parse('some_irrelevant_thing=myfield');

       $this->assertInternalType('array', $result);
       $this->assertArrayHasKey('count_distinct', $result);
       $this->assertFalse($result['count_distinct']);        
    }
}

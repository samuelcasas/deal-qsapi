<?php

namespace Deal\Tests\Modules\Qsapi\Engine;

use Deal\Modules\Qsapi\Engine;

/**
 * Tests for the NestedFilter engine
 */
class NestedFilterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Engine\NestedFilter 
     */
    protected $engine;

    public function setUp()
    {
        $this->engine = new Engine\NestedFilter();
    }
    
    public function testSimpleEqualityOnSingleField()
    {
        $result = $this->engine->parse('filter[myfield]=myvalue');
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('filter', $result);
        $this->assertEquals(array(
            'myfield' => array(
                'value' => 'myvalue',
                'operator' => 'eq',
            ),
        ), $result['filter']);
    }
    
    public function testSimpleEqualityOnMultipleFields()
    {
        $result = $this->engine->parse('filter[myfield1]=myvalue1&filter[myfield2]=myvalue2');
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('filter', $result);
        $this->assertEquals(array(
            'myfield1' => array(
                'value' => 'myvalue1',
                'operator' => 'eq',
            ),
            'myfield2' => array(
                'value' => 'myvalue2',
                'operator' => 'eq',
            ),
        ), $result['filter']);
    }
    
    public function dpTestIntegerIndexedArrayThrowsException()
    {
        return array(
            array('filter=myfield:myvalue'),
            array('filter[]=myfield:myvalue'),

            // for now, this is unimplemented, so expect an exception
            array('filter[myfield][gt]=2000'),
        );
    }
    
    /**
     * @dataProvider dpTestIntegerIndexedArrayThrowsException
     * @expectedException Deal\Modules\Qsapi\Engine\EngineException
     */
    public function testIntegerIndexedArrayThrowsException($qs)
    {
        $result = $this->engine->parse($qs);
    }
    
    public function testNoFiltersReturnsNull()
    {
        $result = $this->engine->parse();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('filter', $result);
        $this->assertNull($result['filter']);
    }
}

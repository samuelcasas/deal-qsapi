<?php

namespace Deal\Tests\Modules\Qsapi\Engine;

use Deal\Modules\Qsapi\Engine;
use Deal\Tests\Modules\Qsapi\BaseTest;

/**
 * Tests for the NestedFilter engine
 */
class NestedFilterTest extends BaseTest
{
    /**
     * @var Engine\NestedFilter 
     */
    protected $engine;

    public function setUp()
    {
        $this->engine = new Engine\NestedFilter();
    }
    
    
    public function dpTestValidFilters()
    {
        return array(
          
            // ===================
            array(
                'Blank',
                '',
                null,
            ),

            // ===================
            array(
                'Null',
                null,
                null,
            ),

            // ===================
            array(
                'Single field, equality',
                'filter[myfield]=myvalue',
                array(
                    'myfield' => array(
                        '$and' => array(
                            array(
                                'value' => 'myvalue',
                                'operator' => 'eq',
                            ),
                        )                        
                    ),
                ),
            ),
            
            // ===================
            array(
                'Multiple fields, equality',
                'filter[myfield1]=myvalue1&filter[myfield2]=myvalue2',
                array(
                    'myfield1' => array(
                        '$and' => array(
                            array(
                                'value' => 'myvalue1',
                                'operator' => 'eq',
                            ),                            
                        ),
                    ),
                    'myfield2' => array(
                        '$and' => array(
                            array(
                                'value' => 'myvalue2',
                                'operator' => 'eq',
                            ),                            
                        ),
                    ),
                ),
            ),
            
            // ===================
            array(
                'Single field, AND',
                'filter[myfield1]=myvalue1,myvalue2',
                array(
                    'myfield1' => array(
                        '$and' => array(
                            array(
                                'value' => 'myvalue1',
                                'operator' => 'eq',
                            ),                                                        
                            array(
                                'value' => 'myvalue2',
                                'operator' => 'eq',
                            ),                                                        
                        ),
                    ),
                ),
            ),
            
            // ===================
            array(
                'Single field, OR',
                'filter[myfield1]=myvalue1|myvalue2',
                array(
                    'myfield1' => array(
                        '$or' => array(
                            array(
                                'value' => 'myvalue1',
                                'operator' => 'eq',
                            ),                                                        
                            array(
                                'value' => 'myvalue2',
                                'operator' => 'eq',
                            ),                                                        
                        ),
                    ),
                ),
            ),
            
            // ===================
            array(
                'Single field, non-equality operator',
                'filter[myfield1][gte]=myvalue',
                array(
                    'myfield1' => array(
                        '$and' => array(
                            array(
                                'value' => 'myvalue',
                                'operator' => 'gte',
                            ),                                                        
                        ),
                    ),
                ),
            ),
            
            // ===================
            array(
                'Single field, multiple non-equality operators',
                'filter[myfield][lte]=myvalue1&filter[myfield][gte]=myvalue2',
                array(
                    'myfield' => array(
                        '$and' => array(
                            array(
                                'value' => 'myvalue1',
                                'operator' => 'lte',
                            ),                                                        
                            array(
                                'value' => 'myvalue2',
                                'operator' => 'gte',
                            ),                                                        
                        ),
                    ),
                ),
            ),
            
        );
    }

    /**
     * @dataProvider dpTestValidFilters
     * @param string $desc
     * @param string $qs
     * @param null|array $expectedResult
     */
    public function testValidFilters($desc, $qs, $expectedResult)
    {
        $result = $this->engine->parse($qs);
        $val = $this->performDrilldownAssertionsAndReturn($result, 'filter');
        $this->assertEquals($expectedResult, $val, $desc);        
    }
        
    public function dpTestBadFormatsThrowException()
    {
        return array(
            array('filter=myfield:myvalue'),
            array('filter[]=myfield:myvalue'),
            array('filter[myfield]=,myvalue'),
            array('filter[myfield]=myvalue,'),
            array('filter[myfield]=myvalue|'),
            array('filter[myfield]=|myvalue'),
            array('filter[myfield]=myvalue1|myvalue2,myvalue3'),
            array('filter[myfield]=myvalue1,myvalue2|myvalue3'),
            array('filter[myfield][badoperator]=myvalue1'),
        );
    }
    
    /**
     * @dataProvider dpTestBadFormatsThrowException
     * @expectedException Deal\Modules\Qsapi\Engine\EngineException
     */
    public function testBadFormatsThrowException($qs)
    {
        $result = $this->engine->parse($qs);
    }
    
    public function testAndWithNoOperators()
    {
        $result = $this->engine->parse('filter[myfield]=val1,val2');
        $and = $this->performDrilldownAssertionsAndReturn($result, 'filter.myfield.$and');
        $this->assertEquals(array(
            array(
                'value' => 'val1',
                'operator' => 'eq',
            ),
            array(
                'value' => 'val2',
                'operator' => 'eq',
            ),
        ), $and);
    }
}

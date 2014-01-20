<?php

namespace Deal\Tests\Modules\Qsapi\Exporter;

use Deal\Modules\Qsapi\Exporter;
use Deal\Tests\Modules\Qsapi\BaseTest;

/**
 * Tests for the MongoArrays Exporter
 *
 * @author David Weinraub <david.weinraub@dws.la.>
 */
class MongoArrayTest extends BaseTest
{
    /**
     *
     * @var Exporter\MongaCallback
     */
    protected $exporter;
    
    /**
     * Empty parse result
     * 
     * @var array
     */
    protected $emptyParseResult = array(
        'order' => null,
        'fields' => null,
        'exclude' => null,
        'filter' => null,
        'limit' => null,
        'page' => null,
    );
    
    public function setUp()
    {
        $this->exporter = new Exporter\MongoArray(10);
    }
    
    public function dpTestOrderSingleField()
    {
        return array(
            array('asc', 1),
            array('desc', -1),
        );
    }
    
    public function testSkip()
    {
        $original = array_merge($this->emptyParseResult, array(
            'limit' => 10,
            'page'  => 3,
        ));
        $result = $this->exporter->export($original);
        $actual = $this->performDrilldownAssertionsAndReturn($result, 'skip');
        $this->assertEquals(20, $actual);
    }

    public function testLimit()
    {
        $original = array_merge($this->emptyParseResult, array(
            'limit' => 20,
        ));
        $result = $this->exporter->export($original);
        $actual = $this->performDrilldownAssertionsAndReturn($result, 'limit');
        $this->assertEquals(20, $actual);
    }

    /**
     * @dataProvider dpTestOrderSingleField
     */
    public function testOrderSingleField($originalDirection, $expectedDirection)
    {
        $original = array_merge($this->emptyParseResult, array(
            'order' => array(
               'myfield' => $originalDirection,
            ),
        ));
        $expected = array(
            'myfield' => $expectedDirection,
        );
        $result = $this->exporter->export($original);
        $actual = $this->performDrilldownAssertionsAndReturn($result, 'sort');
        $this->assertEquals($expected, $actual);
    }
    
    public function testOrderMultipleFields()
    {
        $original = array_merge($this->emptyParseResult, array(
            'order' => array(
               'myfield1' => 'desc',
               'myfield2' => 'asc',
            ),
        ));
        $expected = array(
            'myfield1' => -1,
            'myfield2' => 1,
        );
        $result = $this->exporter->export($original);
        $actual = $this->performDrilldownAssertionsAndReturn($result, 'sort');
        $this->assertEquals($expected, $actual);        
    }
    
    public function testWhereWithSingleFieldEquality()
    {
        $original = array_merge($this->emptyParseResult, array(
            'filter' => array(
                'myfield' => array(
                    '$and' => array(
                        array(
                            'value' => 'myvalue',
                            'operator' => 'eq',
                        ),
                    )                        
                ),
            ),
        ));        
        $expected = array(
            '$and' => array(
                array(
                    '$and' => array(
                        array(
                            'myfield' => 'myvalue',
                         ),
                    ),
                ),
            ),
        );
        $result = $this->exporter->export($original);
        $actual = $this->performDrilldownAssertionsAndReturn($result, 'filter');
        $this->assertEquals($expected, $actual);        
    }
    
    public function testWhereWithMultipleFieldsUsingEquality()
    {
        $original = array_merge($this->emptyParseResult, array(
            'filter' => array(
                'myfield1' => array(
                    '$and' => array(
                        array(
                            'value' => 'myvalue1',
                            'operator' => 'eq',
                        ),
                    )                        
                ),
                'myfield2' => array(
                    '$and' => array(
                        array(
                            'value' => 'myvalue2',
                            'operator' => 'eq',
                        ),
                    )                        
                ),
            ),
        ));        
        $expected = array(
            '$and' => array(
                array(
                    '$and' => array(
                        array(
                            'myfield1' => 'myvalue1',                    
                        ),
                    ),
                ),
                array(
                    '$and' => array(
                        array(
                            'myfield2' => 'myvalue2',                    
                        ),
                    ),
                ),
            ),
        );
        $result = $this->exporter->export($original);
        
        $actual = $this->performDrilldownAssertionsAndReturn($result, 'filter');       
        $this->assertEquals($expected, $actual);        
    }
    
    public function testWhereSingleFieldGreaterThan()
    {
       $original = array_merge($this->emptyParseResult, array(
            'filter' => array(
                'myfield' => array(
                    '$and' => array(
                        array(
                            'value' => 'myvalue',
                            'operator' => 'gt',
                        ),
                    )                        
                ),
            ),
        ));        
        $expected = array(
            '$and' => array(
                array(
                    '$and' => array(
                        array(
                            'myfield' => array(
                                '$gt' => 'myvalue',
                            ),                            
                        ),
                    ),
                ),
            ),
        );
        $result = $this->exporter->export($original);
        $actual = $this->performDrilldownAssertionsAndReturn($result, 'filter');
        $this->assertEquals($expected, $actual);
    }

    public function dpTestWhereThrowsExceptionOnNonArrayValues()
    {
        return array(

            array(
                array(
                    'myfield' => 'should-be-array',
                ),
            ),
            
//            array(
//                array(
//                    'myfield' => array(
//                        '$and' => 'should-be-array',
//                    ),
//                ),
//            ),
//            
            array(
                array(
                    'myfield' => array(
                        '$and' => array(
                            'should-be-array'
                        ),
                    ),
                ),
            ),
            
            array(
                array(
                    'myfield' => array(
                        '$and' => array(
                            array(
                                // missing value here
                                'operator' => 'gt',
                            ),
                        ),
                    ),
                ),
            ),
            
            array(
                array(
                    'myfield' => array(
                        '$and' => array(
                            array(
                                'value' => 'myvalue',
                                // missing operator here
                            ),
                        ),                   
                    ),
                ),
            ),
            
            array(
                array(
                    'myfield' => array(
                        '$invalidOperator' => array(
                            array(
                                'value' => 'myvalue',
                                'operator' => 'eq',
                            ),
                        ),
                    ),
                ),
            ),
            
        );
    }
    
    /**
     * @dataProvider dpTestWhereThrowsExceptionOnNonArrayValues
     * @expectedException Deal\Modules\Qsapi\Exporter\ExporterException
     */
    public function testWhereThrowsExceptionOnNonArrayValues($filterContent)
    {
       $original = array_merge($this->emptyParseResult, array(
            'filter' => $filterContent,
        ));
       $this->exporter->export($original);
    }
    
    public function testFields()
    {
        $original = array_merge($this->emptyParseResult, array(
            'fields' => array(
                'myincludedfield1',
                'myincludedfield2',
            ),
            'exclude' => array(
                'myexcludedfield1',
                'myexcludedfield2',
            ),
        ));
        $result = $this->exporter->export($original);
        $expected = array(
            'myincludedfield1' => true,
            'myincludedfield2' => true,
            'myexcludedfield1' => false,
            'myexcludedfield2' => false,
        );
        $this->assertEquals($expected,$result['fields']);
    }
}

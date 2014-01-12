<?php

namespace Deal\Tests\Modules\Qsapi\Exporter;

use Deal\Modules\Qsapi\Exporter;
use Deal\Tests\Modules\Qsapi\BaseTest;
use Monga\Query;

/**
 * Tests for the Monga Exporter
 *
 * @author David Weinraub <david.weinraub@dws.la.>
 */
class MongaTest extends BaseTest
{
    /**
     *
     * @var Exporter\Monga
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
        $this->exporter = new Exporter\Monga(10);
    }
    
    /**
     * A utulity method ripped from the Monga\Query\Find tests for inspecting
     * the value of a Find object.
     * 
     * @param string $property
     * @return mixed
     */
    protected function getProperty($find, $property)
    {
        $reflection = new \ReflectionObject($find);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($find);
    }
    
    /**
     * Utility function to apply closure to query
     * 
     * @param Closure $closure
     * @param \Monga\Query\Find $query
     * @return \Monga\Query\Find
     */
    protected function applyClosureToQuery($closure, Query\Find $query = null)
    {
        return $query;
    }

    /**
     * Utility method to facilitate testing
     * 
     * @param array $original
     * @param \Monga\Query\Find $query
     * @return \Monga\Query
     */
    protected function exportAndApplyClosureToQuery($original, Query\Find $query = null)
    {
        if (!$query) {
            $query = new Query\Find();
        }
        $result = $this->exporter->export($original);
        $closure = $result['queryClosure'];
        $closure($query);
        return $query;
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
        $query = $this->exportAndApplyClosureToQuery($original);
        $actual = $this->getProperty($query, 'skip');
        $this->assertEquals(20, $actual);
    }

    public function testLimit()
    {
        $original = array_merge($this->emptyParseResult, array(
            'limit' => 20,
        ));
        $query = $this->exportAndApplyClosureToQuery($original);
        $actual = $this->getProperty($query, 'limit');
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
        $query = $this->exportAndApplyClosureToQuery($original);
        $expected = array(
            'myfield' => $expectedDirection,
        );
        $actual = $this->getProperty($query, 'orderBy');
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
        $query = $this->exportAndApplyClosureToQuery($original);
        $expected = array(
            'myfield1' => -1,
            'myfield2' => 1,
        );
        $actual = $this->getProperty($query, 'orderBy');
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
        $query = $this->exportAndApplyClosureToQuery($original);
        $expected = array(
                'myfield' => 'myvalue',
        );
        $actual = $query->getWhere();        
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
        $query = $this->exportAndApplyClosureToQuery($original);
        $expected = array(
                'myfield1' => 'myvalue1',
                'myfield2' => 'myvalue2',
        );
        $actual = $query->getWhere();        
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
        $query = $this->exportAndApplyClosureToQuery($original);
        $expected = array(
                'myfield' => array(
                    '$gt' => 'myvalue',
                ),
        );
        $actual = $query->getWhere();         
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
            
            array(
                array(
                    'myfield' => array(
                        '$and' => array(
                            array(
                                // missing value here
                                'operator' => 'gt',
                            ),
                        )                        
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
                        )                        
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
                        )
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
        $query = $this->exportAndApplyClosureToQuery($original);        
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

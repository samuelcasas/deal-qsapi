<?php

namespace Deal\Modules\Qsapi\Engine;

/**
 * An engine that handles filtering using nested syntax:
 * 
 * ?filter[myfield][myoperator][mymodifier]=myvalue
 */
class NestedFilter extends AbstractEngine
{    
    
    /**
     * List of valid operators
     * 
     * @var array
     */
    protected $validOperators = array(
        'eq',
        'neq',
        'gt',
        'gte',
        'lt',
        'lte',
    );
    
    /**
     * Parse for filters
     *  
     * @param array $arr
     * @return null|array
     */
    protected function parseFilter(array $arr)
    {
        if (!array_key_exists('filter', $arr)) {
            return null;
        }
        $filter = $arr['filter'];
        
        $result = array();
        
        if (!is_array($filter)) {
            throw new EngineException('Filter must be an array');
        }
        foreach ($filter as $field => $filterData) {
            if (is_integer($field)) {
                throw new EngineException('Filter keys must be field names');
            }
            if (is_string($filterData)) {
                $result[$field] = $this->parseFilterString($filterData);
            } else {
                $result[$field] = $this->parseFilterArray($filterData);
            }
        }
        return $result;
    }
    
    /**
     * Parse formats:
     *      ?filter[myfield]=myvalue
     *      ?filter[myfield]=val1|val2|val3
     *      ?filter[myfield]=val1,val2,val3
     * 
     * @param string $str
     * @return array
     */
    protected function parseFilterString($str)
    {   
        $posPipe = strpos($str, '|');
        $posComma = strpos($str, ',');
        
        if (false === $posPipe && false === $posComma) {
            return array(
                '$and' => array(
                    array(
                        'value' => $str,
                        'operator' => 'eq',
                    ),                    
                ),
            );
        }

        if (is_int($posPipe) && $posPipe >= 0 && is_int($posComma) && $posComma >=0) {
            throw new EngineException('Cannot use pipe and comma in single QS value string');
        }
        if (0 === $posComma) {
            throw new EngineException('Cannot start QS value string with comma');
        }        
        if (0 === $posPipe) {
            throw new EngineException('Cannot start QS value string with pipe');
        }
        
        $return = array();
        if ($posComma > 0) {
            foreach(explode(',', $str) as $s) {
                if (strlen($s) == 0) {
                    throw new EngineException('Cannot use blank string in QS "and" condition');
                }
                $return['$and'][] = array(
                    'value' => $s,
                    'operator' => 'eq',
                );
            }
            return $return;
        }
        if ($posPipe > 0) {
            foreach(explode('|', $str) as $s) {
                if (strlen($s) == 0) {
                    throw new EngineException('Cannot use blank string in QS "or" condition');
                }
                $return['$or'][] = array(
                    'value' => $s,
                    'operator' => 'eq',
                );
            }
            return $return;
        }
    }
    
    /**
     * Parse format ?filter[myfield][myoperator]=myvalue
     *  
     * @param array $arr
     * @throws EngineException
     * @return array
     */
    protected function parseFilterArray(array $arr)
    {
        $return  = array();
        foreach ($arr as $operator => $val) {
            if (!in_array($operator, $this->validOperators)) {
                throw new EngineException('Invalid QS operator: ' . $operator);
            }
            $return['$and'][] = array(
                'value' => $val,
                'operator' => $operator,
            );            
        }
        return $return;
    }

 }

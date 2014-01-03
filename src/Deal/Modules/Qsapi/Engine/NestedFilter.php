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
            if (!is_string($filterData)) {
                throw new EngineException('At present, only strings for filter values are supported. Stay tuned.');
            }
            $result[$field]['value'] = $filterData;
            $result[$field]['operator'] = 'eq';
        }
        return $result;
    }

 }

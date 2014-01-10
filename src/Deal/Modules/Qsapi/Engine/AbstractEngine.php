<?php

namespace Deal\Modules\Qsapi\Engine;

use Deal\Modules\Qsapi\Util\String as StringUtil;

/**
 * An engine that handles filtering using nested syntax:
 * 
 * ?filter[myfield][myoperator][mymodifier]=myvalue
 */
abstract class AbstractEngine implements EngineInterface
{

    /**
     * Valid orders mapped to canonical order
     * 
     * @var array
     */
    protected $validDirections = array(
        'a'          => 'asc',
        'asc'        => 'asc',
        'ascending'  => 'asc',
        'd'          => 'desc',
        'desc'       => 'desc',
        'descending' => 'desc',
    );
    
    /**
     * Return whatever parse_str gives us
     * 
     * @param string $qs
     * @return array
     * @see EngineInterface
     */
    public function parse($qs = '')
    {
        parse_str($qs, $arr);
        $this->parseArray($arr);
        return $this->result;
    }
    
    /**
     * Sets $this->result as the result of parsing all the stuff
     * 
     * @param array $arr
     */
    protected function parseArray(array $arr)
    {
       $result = array();
       
       $result['filter'] = $this->parseFilter($arr);
       $result['fields'] = $this->parseFields($arr);
       $result['exclude'] = $this->parseExclude($arr);
       $result['order'] = $this->parseOrder($arr);
       $result['limit'] = $this->parseLimit($arr);
       $result['page'] = $this->parsePage($arr);
       $result['count_distinct'] = $this->parseCountDistinct($arr);
       
       $this->result = $result;
    }
    
    /**
     * 
     * @param array $arr
     * @return null|array
     */
    abstract protected function parseFilter(array $arr);

    /**
     * Include fields
     * 
     * @param array $arr
     * @return null|array
     */
    protected function parseFields(array $arr)
    {
        if (!array_key_exists('fields', $arr)) {
            return null;
        }
        $fields = $arr['fields'];
        if (!is_string($fields)) {
            throw new EngineException('Fields should be comma-separated string');
        }
        return explode(',', $fields);
    }

    /**
     * Exclude fields
     * 
     * @param array $arr
     * @return null|array
     */
    protected function parseExclude(array $arr)
    {
        if (!array_key_exists('exclude', $arr)) {
            return null;
        }
        $exclude = $arr['exclude'];
        if (!is_string($exclude)) {
            throw new EngineException('Fields should be comma-separated string');
        }
        return explode(',', $exclude);
    }

    /**
     * 
     * @param array $arr
     * @return null|array
     * @throws EngineException
     */
    protected function parseOrder(array $arr)
    {
        if (!array_key_exists('order', $arr)) {
            return null;
        }
        
        $return = array();
        
        $orders = $arr['order'];
        if (!is_string($orders)) {
            throw new EngineException('Fields should be comma-separated string');
        }
        $pairs = explode(',', $orders);
        
        foreach ($pairs as $pair) {
            $pairComp = explode(':', $pair);
            if (count($pairComp) != 2) {
                throw new EngineException('Order must be in format: ?field1:dir1,field2:dir2');
            }            
            list($field, $direction) = $pairComp;            
            $direction = $this->getCanonicalDirection($direction);
            $return[$field] = $direction;
        }
        return $return;
    }
    
    protected function getCanonicalDirection($direction)
    {
        $direction = strtolower($direction);
        if (!in_array($direction, array_keys($this->validDirections))) {
            throw new EngineException('Valid order directions are: ' . implode(', ', array_keys($this->validDirections)));                
        }
        return $this->validDirections[$direction];
    }

    /**
     * 
     * @param array $arr
     * @return null|array
     */
    protected function parseLimit(array $arr)
    {
        return $this->parseIntegerField('limit', $arr);
    }

    /**
     * Parse for the page variable
     * 
     * @param array $arr
     * @return null|array
     */
    protected function parsePage(array $arr)
    {
        return $this->parseIntegerField('page', $arr);
    }
    
    protected function parseIntegerField($field, array $arr)
    {
        if (!array_key_exists($field, $arr)) {
            return null;
        }
        $value = $arr[$field];
        if (!StringUtil::isInteger($value)) {
            throw new EngineException(sprintf('%s must be integer', ucfirst($field)));
        }
        if ($value <= 0) {
            throw new EngineException(sprintf('%s must be positive', ucfirst($field)));
        }
        return $value;        
    }
    
    protected function parseCountDistinct($arr)
    {
        if (!array_key_exists('count_distinct', $arr)) {
            return false;
        }
        $countDistinct = $arr['count_distinct'];
        if (!is_string($countDistinct)) {
            throw new EngineException('count_distinct should be a single field name');
        }
        return $countDistinct;        
    }
}

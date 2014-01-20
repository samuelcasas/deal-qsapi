<?php

namespace Deal\Modules\Qsapi\Exporter;

use Deal\Modules\Qsapi\Exporter;
use Monga\Query\Find;

/**
 * Populates a Monga Find object and a fields array using the values from the QS engine
 *
 * @author David Weinraub <david.weinraub@dws.la.>
 */
class MongaCallback implements ExporterInterface
{

    /**
     * PerPage value to use for skip
     * 
     * @var int 
     */
    protected $limit = null;
    
    /**
     * The qsArray on which we are operating
     * 
     * @var array
     */
    protected $qsArray;
    
    protected $mapOperatorToMethod = array(
        '$or' => 'orWhere',
        '$and' => 'andWhere',
    );
    
    /**
     * Constructor
     * 
     * @param int $limit optional.
     */
    public function __construct($limit = null)
    {
        if ($limit) {
            $this->limit = $limit;
        }
    }
    
    /**
     * Returns a structure perfectly suited to adding to $collection->find($query, $fields)
     * 
     * Format: 
     * 
     *      $return = array(
     *          'query' => $query // a Monga/Query/Find instance
     *          'fields => $fields // an associative array of +1/-1, keyed by field name 
     *      );
     * 
     * @see ExporterInterface
     * @param array $qsArray
     * @return array
     */
    public function export(array $qsArray)
    {
        // Stash the qsArray for easy access. Simplified method signatures below.
        $this->qsArray = $qsArray;
        
        // Reference to $this for use in closure below
        $me = $this;
        
        // return
        $queryClosure = function(Find $query) use ($me) {            
            $me->addWhere($query);
            $me->addOrder($query);
            $me->addSkip($query);
            $me->addLimit($query);            
        };
        $fields = $this->getFields();
        return array(
            'queryClosure' => $queryClosure, 
            'fields' => $fields,
        );
    }
    
    /**
     * Add where/conditions to query
     * 
     * @param \Deal\Modules\Qsapi\Exporter\Query $query
     * @return void
     */
    protected function addWhere(Find $query)
    {
        if (!is_array($this->qsArray['filter'])) {
            return array();
        }
        foreach ($this->qsArray['filter'] as $field => $filterData) {
            
            if (!is_array($filterData)) {
                throw new Exporter\ExporterException(sprintf('Filter data for field %s from engine must be an array', $field));
            }
            
            // push down
            $logicalOperator = key ($filterData);
            $filterData = $filterData[$logicalOperator];
            
            foreach ($filterData as $fd) {                
                $method = $this->buildWhereMethod($field, $logicalOperator, $fd);
                $query->$method($field, $fd['value']);
            }
        }
    }
    
    /**
     * 
     * @param string $field
     * @param string $logicalOperator
     * @param array $moreFilterData
     * @throws Exporter\ExporterException
     */
    protected function buildWhereMethod($field, $logicalOperator, array $moreFilterData)
    {        
        if (!array_key_exists($logicalOperator, $this->mapOperatorToMethod)) {
            throw new Exporter\ExporterException(sprintf('Invalid logical operator %s for field %s', $logicalOperator, $field));
        }
        if (!array_key_exists('operator', $moreFilterData)) {
            throw new Exporter\ExporterException(sprintf('No operator specified for field %s', $field));
        }
        if (!array_key_exists('value', $moreFilterData)) {
            throw new Exporter\ExporterException(sprintf('No value specified for field %s', $field));
        }
        $methodSuffix = $moreFilterData['operator'];
        if ('eq' == $methodSuffix) {
            $methodSuffix = '';
        }
        return $this->mapOperatorToMethod[$logicalOperator] . ucfirst($methodSuffix);
    }

    /**
     * Add order to query
     * 
     * @param \Monga\Query\Find $query
     * @return void
     */
    protected function addOrder(Find $query)
    {
        if (!is_array($this->qsArray['order'])) {
            return;
        }
        foreach ($this->qsArray['order'] as $field => $direction) {
            $query->orderBy($field, $direction == 'asc' ? 1 : -1);            
        }
    }
    
    /**
     * Add skip to query
     * 
     * @param \Monga\Query\Find $query
     * return void
     */
    protected function addSkip(Find $query)
    {
        $limit = $this->qsArray['limit'] ?: $this->limit;        
        if ($limit) {
            $page = $this->qsArray['page'] ?: 1;
            $skip = ($page - 1) * $limit;
            $query->skip($skip);
        }
    }
    
    /**
     * Add limit to query
     * 
     * @param \Monga\Query\Find $query
     */
    protected function addLimit(Find $query)
    {
        $limit = $this->qsArray['limit'] ?: $this->limit;
        if ($limit) {
            $query->limit($limit);
        }
    }
    
    /**
     * Build fields array. 
     * 
     * Format:
     * 
     *      $fields = array(
     *          'myfield1' => 1 // for asc
     *          'myfield2' => -1 // for desc
     *      );
     * 
     * @return array
     */
    protected function getFields()
    {
        $fields = array();
        if (is_array($this->qsArray['fields'])) {
            foreach ($this->qsArray['fields'] as $field) {
                $fields[$field] = true;
            }
        }
        if (is_array($this->qsArray['exclude'])) {
            foreach ($this->qsArray['exclude'] as $field) {
                $fields[$field] = false;
            }
        }
        return $fields;
    }
}

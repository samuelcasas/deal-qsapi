<?php

namespace Deal\Modules\Qsapi\Exporter;

use Deal\Modules\Qsapi\Exporter;

/**
 * Creates an array structure easily usable for Monga
 *
 * @author David Weinraub <david.weinraub@dws.la.>
 */
class MongoArray implements ExporterInterface
{

    /**
     * Default limit to use for skip
     * 
     * @var int 
     */
    protected $defaultLimit = null;
    
    protected $logicalOperators = array(
        '$and',
        '$or',
    );
        
    /**
     * The qsArray on which we are operating
     * 
     * @var array
     */
    protected $qsArray;
    
    /**
     * Constructor
     * 
     * @param int $defaultLimit optional.
     */
    public function __construct($defaultLimit = null)
    {
        if ($defaultLimit) {
            $this->defaultLimit = $defaultLimit;
        }
    }
    
    /**
     * Returns a structure perfectly suited to use with Monga.
     * 
     * Format: 
     * 
     *      $return = array(
     *          'filter' => $query // a Monga/Query/Find instance
     *          'fields => $fields // an associative array of +1/-1, keyed by field name 
     *          'sort => $sort // an associative array of +1/-1, keyed by field name 
     *          'limit => $limit // integer
     *          'skip => $skip // integer
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
        
        // return
        $filter = $this->getFilter();
        $fields = $this->getFields();
        $sort = $this->getSort();
        $limit = $this->getLimit();
        $skip = $this->getSkip();
        
        return compact('filter', 'fields', 'sort', 'limit', 'skip');
    }
    
    /**
     * Extract the filter conditions as an array
     * 
     * @param \Deal\Modules\Qsapi\Exporter\Query $query
     * @return array
     */
    protected function getFilter()
    {
        if (!is_array($this->qsArray['filter'])) {
            return array();
        }
        
        $return = array();
        
        foreach ($this->qsArray['filter'] as $field => $filterData) {
            
            if (!is_array($filterData)) {
                throw new Exporter\ExporterException(sprintf('Filter data for field %s from engine must be an array', $field));
            }

            // grab the logical operator and push down            
            $logicalOperator = key($filterData);
            
            if (!in_array($logicalOperator, $this->logicalOperators)) {
                throw new Exporter\ExporterException('Invalid logical operator: ' . $logicalOperator);
            }
            $filterData = $filterData[$logicalOperator];
            
            // conditions for this field, to be united under the logical operator
            $conditions = array();
            
            // a bunch of regular operators: 'eq', 'gt', etc
            foreach ($filterData as $fd) {
                
                if (!is_array($fd)) {
                    throw new Exporter\ExporterException(sprintf('Operator/value for field %s should be in array form', $field));
                }
                if (!array_key_exists('operator', $fd)) {
                    throw new Exporter\ExporterException(sprintf('Missing operator for field %s in payload provided by engine', $field));
                }
                if (!array_key_exists('value', $fd)) {
                    throw new Exporter\ExporterException(sprintf('Missing value for field %s in payload provided by engine', $field));
                }

                $conditions[] = $this->buildQueryExpression($field, $fd);                       
            }

            // add another entry to the top-level 'and'
            $return['$and'][] = array(
                $logicalOperator => $conditions,
            );
        }
        
        return $return;
    }
    
    /**
     * Get the Mongo query expression for a field and a filterData entry
     * 
     * @param string $field
     * @param array $fd
     * @return type
     */
    protected function buildQueryExpression($field, array $fd)
    {
        $queryValue = ('eq' == $fd['operator']) ? $fd['value'] : array(
            '$' . $fd['operator'] => $fd['value'],
        );
        return array(
            $field => $queryValue,
        );
    }
    
    /**
     * Add order to query
     * 
     * @param \Monga\Query\Find $query
     * @return void
     */
    protected function getSort()
    {
        if (!is_array($this->qsArray['order'])) {
            return;
        }
        $return = array();
        foreach ($this->qsArray['order'] as $field => $direction) {
            $return[$field] = $direction == 'asc' ? 1 : -1;
        }
        return $return;
    }
    
    /**
     * Get skip
     * 
     * return void
     */
    protected function getSkip()
    {
        $limit = $this->qsArray['limit'] ?: $this->defaultLimit;        
        $page = $this->qsArray['page'] ?: 1;
        return ($page && $limit) ? ($page - 1) * $limit : 0;
    }
    
    /**
     * Add limit to query
     * 
     * @param \Monga\Query\Find $query
     */
    protected function getLimit()
    {
        return $this->qsArray['limit'] ?: $this->defaultLimit;
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

<?php

namespace Deal\Modules\Qsapi\Engine;

/**
 * A stupid engine implementation that returns only a fixed array as a parsing 
 * result.
 */
class Fixed implements EngineInterface
{

    /**
     * The fixed result to return
     * 
     * @var array
     */
    protected $result;
    
    /**
     * Constructor
     * 
     * @param array $result
     */
    public function __construct(array $result)
    {
        $this->result = $result;
    }
    
    /**
     * Return the fixed return result
     * 
     * @param string $qs
     * @return array
     * @see EngineInterface
     */
    public function parse($qs = '')
    {
        return $this->result;
    }    
}

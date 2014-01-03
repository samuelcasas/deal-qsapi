<?php

namespace Deal\Modules\Qsapi\Engine;

/**
 * A stupid engine implementation that returns only what str_parse gives us
 */
class PhpParseStr implements EngineInterface
{
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
        return $arr;
    }
}

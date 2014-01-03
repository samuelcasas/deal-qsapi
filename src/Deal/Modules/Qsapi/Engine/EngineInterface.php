<?php

namespace Deal\Modules\Qsapi\Engine;

/**
 * An interface for QSAPI parser engines
 * 
 * @author David Weinraub <david.weinraub@dws.la.>
 */
interface EngineInterface
{
    /**
     * Parse the given query strong
     * 
     * @param string $qa The query string to parse
     * @return array An array representation of the parsed query string
     */
    public function parse($qs = '');
}

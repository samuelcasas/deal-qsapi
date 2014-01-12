<?php

namespace Deal\Modules\Qsapi\Exporter;

/**
 * An interface for QSAPI exporters
 * 
 * @author David Weinraub <david.weinraub@dws.la.>
 */
interface ExporterInterface
{
    /**
     * Export the given array - in native Deal\Modules\Qsapi\Parser\Parser - format
     * into an alternative format
     * 
     * @see Deal\Modules\Qsapi\Parser\Parser
     * @param array $qsArray
     * @return array|mixed
     */
    public function export(array $qsArray);
}

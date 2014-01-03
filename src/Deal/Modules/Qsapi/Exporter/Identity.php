<?php

namespace Deal\Modules\Qsapi\Exporter;

/**
 * An dumb exporter. Simply returns whatever he is given
 *
 * @author David Weinraub <david.weinraub@dws.la.>
 */
class Identity implements ExporterInterface
{

    /**
     * Simply returns exactly what he is given
     * 
     * @see ExporterInterface
     * @param array $qsArray
     * @return array
     */
    public function export(array $qsArray)
    {
        return $qsArray;
    }

}

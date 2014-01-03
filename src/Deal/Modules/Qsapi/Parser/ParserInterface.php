<?php

namespace Deal\Modules\Qsapi\Parser;

use ParserException;

/**
 * A QSAPI Parser interface
 */
interface ParserInterface
{
    /**
     * @param string $qs the actual querystring
     * @return array
     * @throws ParserException
     */
    public function parse($qs = '');
}

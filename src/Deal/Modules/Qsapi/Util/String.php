<?php

namespace Deal\Modules\Qsapi\Util;


class String
{

     /**
     * Utility function to validate that a string represents an integer value
     *
     * @see http://stackoverflow.com/a/2012271/131824
     * @param string $s
     * @return boolean
     */
    public static function isInteger($s)
    {
        $s = filter_var($s, FILTER_VALIDATE_INT);
        return ($s !== false);
    }

}

<?php

namespace App;

class File
{

    /**
     * @param $fullPath
     * @param $mode
     * @return bool|resource
     */
    public function open($fullPath, $mode)
    {
        return fopen($fullPath, $mode);
    }

    /**
     *
     * @param $handle
     */
    public function close($handle)
    {
        fclose($handle);
    }
}
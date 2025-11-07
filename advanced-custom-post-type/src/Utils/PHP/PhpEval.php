<?php

namespace ACPT\Utils\PHP;

class PhpEval
{
    /**
     * Substitutes PHP eval()
     *
     * @param $phpCode
     * @param array $attributes
     * @return mixed
     */
    public static function evaluate($phpCode, array $attributes = [])
    {
        $tmpfname = tempnam("/tmp", "PhpEval");
        $handle = fopen($tmpfname, "w+");
        fwrite($handle,  $phpCode);
        fclose($handle);

        if(!empty($attributes)){
            extract($attributes, EXTR_PREFIX_SAME,'' );
        }

        $php = include $tmpfname;
        unlink($tmpfname);

        return $php;
    }
}
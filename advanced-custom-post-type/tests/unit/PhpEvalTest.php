<?php

namespace ACPT\Tests;

use ACPT\Utils\PHP\PhpEval;

class PhpEvalTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function can_eval_simple_php_string_code()
    {
        $string = '<?php $node = 333; return $node; ?>';
        $expected = 333;

        $code = PhpEval::evaluate($string);
        $this->assertEquals($expected, $code);
    }
}

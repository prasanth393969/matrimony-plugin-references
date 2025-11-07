<?php

namespace ACPT\Tests;

use ACPT\Core\Helper\Ulid;

class UlidTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function generate()
    {
        $first  = Ulid::generate(new \DateTime());
        $second = Ulid::generate(new \DateTime());
        $third  = Ulid::generate(new \DateTime());

        $this->assertTrue(Ulid::isValid($first));
        $this->assertTrue(Ulid::isValid($second));
        $this->assertTrue(Ulid::isValid($third));

        $this->assertNotEquals($first, $second);
        $this->assertNotEquals($first, $third);
        $this->assertNotEquals($second, $third);
    }
}
<?php

namespace ACPT\Tests;

use ACPT\Core\Helper\Uuid;

class UuidTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function v1()
    {
        $first  = Uuid::v1(new \DateTime());
        $second = Uuid::v1(new \DateTime());
        $third  = new Uuid(UUid::TYPE_1, new \DateTime());

        $this->assertNotEquals($first, $second);
        $this->assertNotEquals($first, $third);
        $this->assertNotEquals($second, $third);

        $this->assertTrue(Uuid::isV1Valid($first));
        $this->assertTrue(Uuid::isV1Valid($second));
        $this->assertTrue(Uuid::isV1Valid($third));

        $this->assertFalse(Uuid::isV4Valid($first));
        $this->assertFalse(Uuid::isV4Valid($second));
        $this->assertFalse(Uuid::isV4Valid($third));

        $this->assertFalse(Uuid::isV7Valid($first));
        $this->assertFalse(Uuid::isV7Valid($second));
        $this->assertFalse(Uuid::isV7Valid($third));
    }

    /**
     * @test
     */
    public function v4()
    {
        $v4 = Uuid::v4();

        $this->assertTrue(Uuid::isV4Valid($v4));
        $this->assertFalse(Uuid::isV1Valid($v4));
        $this->assertFalse(Uuid::isV7Valid($v4));
    }

    /**
     * @test
     */
    public function v7()
    {
        $first  = Uuid::v7(new \DateTime());
        $second = Uuid::v7(new \DateTime());
        $third  = Uuid::v7(new \DateTime());

        $this->assertFalse(Uuid::isV1Valid($first));
        $this->assertFalse(Uuid::isV1Valid($second));
        $this->assertFalse(Uuid::isV1Valid($third));

        $this->assertFalse(Uuid::isV4Valid($first));
        $this->assertFalse(Uuid::isV4Valid($second));
        $this->assertFalse(Uuid::isV4Valid($third));

        $this->assertTrue(Uuid::isV7Valid($first));
        $this->assertTrue(Uuid::isV7Valid($second));
        $this->assertTrue(Uuid::isV7Valid($third));
    }
}
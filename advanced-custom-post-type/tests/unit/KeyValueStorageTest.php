<?php

namespace ACPT\Tests;

use ACPT\Admin\ACPT_Key_Value_Storage;

class KeyValueStorageTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function can_set_and_get_a_string()
    {
        $key = 'key1';
        $value = 'value';

        $this->assertTrue(ACPT_Key_Value_Storage::set($key, $value));
        $this->assertEquals(ACPT_Key_Value_Storage::get($key), $value);
    }

    /**
     * @test
     */
    public function can_set_and_get_an_array()
    {
        $key = 'key2';
        $value = ['value', 'value2', 'value3', 'value4'];

        $this->assertTrue(ACPT_Key_Value_Storage::set($key, $value));
        $this->assertEquals(ACPT_Key_Value_Storage::get($key), $value);
    }

    /**
     * @test
     */
    public function can_set_and_get_an_object()
    {
        $key = 'key3';
        $value = new \stdClass();
        $value->id = 123;
        $value->name = 'Tom';

        $this->assertTrue(ACPT_Key_Value_Storage::set($key, $value));
        $valueFromCache = ACPT_Key_Value_Storage::get($key);

        $this->assertEquals($valueFromCache->id, 123);
        $this->assertEquals($valueFromCache->name, 'Tom');
    }

    /**
     * @test
     */
    public function can_delete_keys()
    {
        $this->assertTrue(ACPT_Key_Value_Storage::delete('key1'));
        $this->assertTrue(ACPT_Key_Value_Storage::delete('key2'));
        $this->assertTrue(ACPT_Key_Value_Storage::delete('key3'));
    }
}
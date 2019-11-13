<?php

namespace Testify\Tests;

use PHPUnit\Framework\TestCase;
use Testify\Utils\Arrays;

class ArraysTest extends TestCase {

    public function testInArrayKey() {
        $data = array(
            array('id' => 'John', 'lot' => 4),
            array('id' => 'Doe', 'lot' => 85),
            array('id' => 'Bar', 'lot' => 610)
        );

        $this->assertFalse(Arrays::in_array_key($data, 'id', 'Foo'));
        $this->assertTrue(Arrays::in_array_key($data, 'id', 'Bar'));
    }
}

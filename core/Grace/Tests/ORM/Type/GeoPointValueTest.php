<?php

namespace Grace\Tests\ORM\Service;

use Grace\ORM\Type\GeoPointValue;

class GeoPointValueTest extends \PHPUnit_Framework_TestCase
{
    public function testPoint()
    {
        $p1 = GeoPointValue::createFromCommaSeparated('55.75747,37.61795');
        $p2 = GeoPointValue::createFromCommaSeparated('55.75147,37.61195');

        // ожидаем порядка 766 метров
        $this->assertEquals(0.766, round($p1->getDistanceTo($p2), 3));
        $this->assertEquals(0.766, round($p2->getDistanceTo($p1), 3));
        $this->assertEquals(0, round($p1->getDistanceTo($p1), 3));
    }
}

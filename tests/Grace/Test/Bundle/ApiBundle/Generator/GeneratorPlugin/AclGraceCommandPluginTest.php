<?php

namespace Grace\Test\Bundle\ApiBundle\Generator\GeneratorPlugin;

use Grace\Bundle\ApiBundle\Generator\GeneratorPlugin\AclGraceCommandPlugin as Plug;

class AclGraceCommandPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testCasePreparation($expected, $expression)
    {
        $this->assertEquals($expected, Plug::prepareCase($expression));
    }
    public function provider()
    {
        return array(
            array('', ''),
            array('$user->isRole("ROLE_TEST")', 'ROLE_TEST'),
        );
    }
}
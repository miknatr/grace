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
            array('$user->isRole("ROLE_TEST")',                                'ROLE_TEST'),
            array('$user->isRole("ROLE_TEST_UNDERSCORE")',                     'ROLE_TEST_UNDERSCORE'),
            array('$user->isRole("ROLE_TEST") and $user->isRole("ROLE_TEST")', 'ROLE_TEST and ROLE_TEST'),
            array('$user->isRole("ROLE_TEST") and $user->isRole("ROLE_REST")', 'ROLE_TEST and ROLE_REST'),
//            array('$user->isRole("ROLE_TEST")',                                '$user->isRole("ROLE_TEST")'),
//            array('$user->isRole(\'ROLE_TEST\')',                              '$user->isRole(\'ROLE_TEST\')'),

            array('$user->isType("Admin")',                                    'type:Admin'),
            array('$user->isType("HighModerator")',                            'type:HighModerator'),
            array('$user->isType("Admin") and $user->isType("Admin")',         'type:Admin and type:Admin'),
            array('$user->isType("Admin") and $user->isType("Moder")',         'type:Admin and type:Moder'),

            array('$user->getId()',                                            'user:id'),
            array('$user->getCompanyId()',                                     'user:companyId'),

            array('$resource->getId()',                                        'resource:id'),
            array('$resource->getCompanyId()',                                 'resource:companyId'),
        );
    }
}
<?php

namespace Grace\Tests\ORM\Service;

use Grace\ORM\Service\Config\Element\ModelElement;
use Grace\ORM\Service\Config\Element\PropertyElement;
use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Service\Generator;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestModelNames
     */
    public function testAddingToEmptyModel($input, $expected)
    {
        //addGeneratedMethodsToModelContent($content, $class, ModelElement $config)
        $method = (new \ReflectionClass('Grace\\ORM\\Service\\Generator'))->getMethod('addGeneratedMethodsToModelContent');
        $method->setAccessible(true);

        $result = $method->invokeArgs(null, array(file_get_contents($input), $this->prepareModelConfig()));
        $this->assertEquals(file_get_contents($expected), $result);
    }
    public function getTestModelNames()
    {
        $input = glob(__DIR__ . '/GeneratorResources/Model/*.input.php');
        $expected = glob(__DIR__ . '/GeneratorResources/Model/*.expected.php');

        if (count($input) != count($expected)) {
            $this->fail('Каждому input файлу должен соответсвовать expected файл');
        }

        $r = array();
        foreach ($input as $k => $file) {
            /** @noinspection PhpIncludeInspection */
            require_once($input[$k]);
            $r[] = array($input[$k], $expected[$k]);
        }

        return $r;
    }

    public function prepareModelConfig()
    {
        $model = new ModelElement();
        $model->properties['id'] = new PropertyElement();
        $model->properties['id']->mapping = 'int';
        $model->properties['name'] = new PropertyElement();
        $model->properties['name']->mapping = 'string';
        $model->properties['phone'] = new PropertyElement();
        $model->properties['phone']->mapping = 'string';

        return $model;
    }
}

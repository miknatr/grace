<?php
namespace Grace\CGen;
/**
 * Description of TranslateClassGenerator
 *This class must implements all logic of generating abstract classes and his parent classes
 * from parent of ClassParserAbsctract class
 * @author darthvader
 */
class TranslateClassGenerator extends ClassGeneratorAbstract
{
    //put your code here
    private $outputClassName = null;

    public function __construct(string $classDir, $sourceClassName, $outputClassDir, $outputClassName)
    {
    }

    public function setOutputClassName($name)
    {
        $this->outputClassName = $name;
    }

    public function getOutputClassName()
    {
        return $this->outputClassName;
    }

    public function generate()
    {
        //TODO put logic
    }
}
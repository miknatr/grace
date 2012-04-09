<?php
namespace Grace\CGen;
/**
 * Description of ClassParserAbstract
 *
 * @author darthvader
 */
abstract class ClassParserAbstract implements ClassParserInterface {
    //put your code here
    private $instanceClass = null;
    private $instanceParentClass = null;
    
    public function __construct($classname){
        $this->instanceClass = new \ReflectionClass($classname);
        $this->instanceParentClass = new \ReflectionClass($this->instanceClass->getName());
    }

    public function getClassMethods() {
        return $this->instanceClass->getMethods();
    }
    
    public function getClassName() {
        return $this->instanceClass->getName();
    }
    
    public function getClassFields($modifier = NULL) {
        if ($modifier!= NULL){
            switch ($modifier){
            case "public":
                $modifier = '\ReflectionProperty::IS_PUBLIC';
                break;
            case "private":
                $modifier = '\ReflectionProperty::IS_PRIVATE';
                break;
            case "protected":
                $modifier = '\ReflectionProperty::IS_PROTECTED';
                break;
            case "static":
                $modifier = 'ReflectionProperty::IS_STATIC';
                break;
            default: 
                $modifier = NULL;
                break;
            }
        }
        return $this->instanceClass->getProperties($modifier);
    }
    
    //parents
    public function getParentClassName() {
        return $this->instanceParentClass->getName();
    }
    
    public function getParentClassMethods() {
        return $this->instanceParentClass->getMethods();
    }
    
    public function getParentClassFields($modifier = NULL){
        if ($modifier!= NULL){
            switch ($modifier){
            case "public":
                $modifier = '\ReflectionProperty::IS_PUBLIC';
                break;
            case "private":
                $modifier = '\ReflectionProperty::IS_PRIVATE';
                break;
            case "protected":
                $modifier = '\ReflectionProperty::IS_PROTECTED';
                break;
            case "static":
                $modifier = 'ReflectionProperty::IS_STATIC';
                break;
            default: 
                $modifier = NULL;
                break;
            }
        }
        return $this->instanceParentClass->getMethods($modifier);        
    }
}
<?php
namespace Grace\CGen;
/**
 * Description of ClassParserAbstract
 *
 * @author darthvader
 */
use ReflectionClass;
define('GENERATE_NAMESPACE', "Grace\TestNamespace");
abstract class ClassParserAbstract implements ClassParserInterface {
    //put your code here
    private $instanceClass = null;
    private $instanceParentClass = null;
    private $outputDir = ".";
    private $additionalClass = "";
    
    public function __construct($classname, $outputDir, $additionalClass = null){
        $this->setOutputDir($outputDir);
        if ($additionalClass!=null){
            $this->setAdditionalClass ($additionalClass);
        }
        $this->instanceClass = new \ReflectionClass($classname);
        $this->instanceParentClass = new \ReflectionClass($this->instanceClass->getParentClass()->getName());
    }
    
    public function getOutputDir(){
        return $this->outputDir;
    }
    
    public function setOutputDir($dir){
        $this->outputDir = $dir;
    }
    
    public function getAdditionalClass(){
        return $this->additionalClass;
    }
    
    public function setAdditionalClass($addClass){
        $this->additionalClass = $addClass;
    }

    public function getClassMethods($modifier = NULL) {
        if ($modifier!= NULL){
            switch ($modifier){
            case "public":
                $modifier = \ReflectionMethod::IS_PUBLIC;// '\\ReflectionMethod::IS_PUBLIC';
                break;
            case "private":
                $modifier = \ReflectionMethod::IS_PRIVATE;//'\\ReflectionMethod::IS_PRIVATE';
                break;
            case "protected":
                $modifier = \ReflectionMethod::IS_PROTECTED;//'\\ReflectionMethod::IS_PROTECTED';
                break;
            case "static":
                $modifier = \ReflectionMethod::IS_STATIC;//'\\ReflectionMethod::IS_STATIC';
                break;
            default:
                $modifier = NULL;
                break;
            }
        }
        return $this->instanceClass->getMethods($modifier);
    }
    
    public function getClassName() {
        return $this->instanceClass->getShortName();
    }
    
    public function getLongClassName(){
        print_r("\n\n\n\n\n\n======================");
        print_r($this->instanceClass->getName());
        print_r("======================\n\n\n\n\n\n");
        return $this->instanceClass->getName();
    }


    public function getClassFields($modifier = NULL) {
        if ($modifier!= NULL){
            switch ($modifier){
            case "public":
                $modifier = \ReflectionMethod::IS_PUBLIC;
                break;
            case "private":
                $modifier = \ReflectionMethod::IS_PRIVATE;
                break;
            case "protected":
                $modifier = \ReflectionMethod::IS_PROTECTED;
                break;
            case "static":
                $modifier = \ReflectionMethod::IS_STATIC;
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
        return $this->instanceParentClass->getShortName();
    }
    
    public function getLongParentClassName() {
        print_r("\n\n\n\n\n\n======================");
        print_r($this->instanceParentClass->getName());
        print_r("======================\n\n\n\n\n\n");
        return $this->instanceParentClass->getName();
    }
    
    public function getParentClassMethods($modifier = NULL) {
        if ($modifier!= NULL){
            switch ($modifier){
            case "public":
                $modifier = \ReflectionMethod::IS_PUBLIC;
                break;
            case "private":
                $modifier = \ReflectionMethod::IS_PRIVATE;
                break;
            case "protected":
                $modifier = \ReflectionMethod::IS_PROTECTED;
                break;
            case "static":
                $modifier = \ReflectionMethod::IS_STATIC;
                break;
            default: 
                $modifier = NULL;
                break;
            }
        }
        return $this->instanceParentClass->getMethods($modifier);
    }
    
    public function getParentClassFields($modifier = NULL){
        if ($modifier!= NULL){
            switch ($modifier){
            case "public":
                $modifier = \ReflectionMethod::IS_PUBLIC;
                break;
            case "private":
                $modifier = \ReflectionMethod::IS_PRIVATE;
                break;
            case "protected":
                $modifier = \ReflectionMethod::IS_PROTECTED;
                break;
            case "static":
                $modifier = \ReflectionMethod::IS_STATIC;
                break;
            default: 
                $modifier = NULL;
                break;
            }
        }
        /*
        $tmpInstance = $this->instanceClass->getParentClass();
        $fields = $tmpInstance->getProperties($modifier);
        print_r("---------------------------------\n\n\n\n\n");
        print_r($this->instanceParentClass->getProperties($modifier));
        print_r("---------------------------------\n\n\n\n\n");
        */
        return $this->instanceParentClass->getProperties($modifier);
        //return $fields;
    }
}
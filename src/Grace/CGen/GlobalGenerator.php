<?php
namespace Grace\CGen;
/*
 * For use see "example.php"
 */
class GlobalGenerator {
    
    public function generateAbstract($dirYaml = "./yaml", $classesDir = "./classes", $className = "*"){
        $abstractGenerator = new DefaultAbstractClassGenerator($dirYaml, $classesDir, $className);
        try {
            $abstractGenerator->generate();
        }  catch (\Grace\CGen\ClassGeneratorException $ex){
            print_r($ex->getMessage());
        }
        $abstractGenerator = null;
        return true;
    }
    
    public function generateConcrete($dirYaml = "./yaml", $classesDir = "./classes", $className = "*"){
        $concreteGenerator = new DefaultConcreteClassGenerator($dirYaml, $classesDir, $className);
        try {
            $concreteGenerator->generate();
        }  catch (\Grace\CGen\ClassGeneratorException $ex){
            print_r($ex->getMessage());
        }
        $concreteGenerator = null;
        return true;
    }
    
    public function generate($dirYaml = "./yaml", $classesDir = "./classes", $className = "*"){
        $concreteGenerator = new DefaultConcreteClassGenerator($dirYaml, $classesDir, $className);
        try {
            $concreteGenerator->generate();
        }  catch (\Grace\CGen\ClassGeneratorException $ex){
            print_r($ex->getMessage());
        }
        $concreteGenerator = null;
        return true;
    }    
}
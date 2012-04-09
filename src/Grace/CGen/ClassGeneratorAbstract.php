<?php
/*
 */
namespace Grace\CGen;

abstract class ClassGeneratorAbstract implements ClassGeneratorInterface {
    private $config = array(
            "dirYaml" => "../yaml",
            "dirClasses" => "../classes",
            "className" => null
            );
    private $parser = null;
    private $class = null;
    private $parsedFile = null;
    
    public function __construct($config){
        $this->setConfig($config);
    }
    
    public function getParsedFile(){
        return $this->parsedFile;
    }
    
    public function setParsedFile($file){
        $this->parsedFile = $file;
    }
    
    public function getConfig(){
        return $this->config;
    }
    
    public function setConfig($config){
        $this->config = $config;
    }


    public function getDirYaml(){
        return $this->config["dirYaml"];
    }
    
    public function getClassesDir(){
        return $this->config["dirClasses"];
    }
    
    public function getClassName(){
        return $this->config["className"];
    }
    
    public function getVar($var){
        return "\tprivate field".ucfirst($var)."= null;\n";
    }

    public function getGetter($fieldname){
        $str = "\tpublic function get".ucfirst($fieldname)."(\$value) {\n";
        $str .= "\t\t\$this->field".$fieldname." = \$value;\n";
        $str .= "\t}\n";
    }
    
    public function getSetter($fieldname){
        $str = "\tpublic function set".ucfirst($fieldname)."() {\n";
        $str .= "\t\t\return \$this->field".$fieldname.";\n";
        $str .= "\t}\n";
    }
}
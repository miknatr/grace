<?php
/*
 */
namespace Grace\CGen;

abstract class AbstractCGenerator implements InterfaceCGenerator {
    private $config = array(
            "dirYaml" => "../yaml",
            "dirClasses" => "../classes",
            "className" => null
            );
    private $parser = null;
    private $class = null;
    
    public function __construct(array $config){
        $this->config = $config;
    }
    
    public function getConfig(){
        return $this->config;
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
}
?>

<?php
/**
 * TRASH - old version with all fields
 * Description of DefaultCGenerator
 *
 * @author darthvader
 * @var $dirYaml default ../yaml - catalog with classes in Yaml
 * @var $classesDir default ../dirClasses - catalog with classes from Yaml
 * @var $className default ../yaml - classname
 * 
 */
namespace Grace\CGen;

class DefaultClassGenerator extends ClassGeneratorAbstract{
    private $parsedFile = null;
    
    public function __construct($dirYaml, $classesDir, $className){
        $this->config['dirYaml'] = $dirYaml;
        $this->config['dirClasses'] = $classesDir;
        $this->config['className'] = $className;
    }
    
    public function genYamlClass() {
        $this->parseYamlClass();
        $file = $this->generateClass($this->parsedFile);
        try {
            if ($this->writeClass($this->getClassesDir(), $this->getClassName(), $file)){
                return true;
            }else{
                return false;
            }
        }catch (ErrorException $ex){
            //TODO add exceptions
            die($ex->getMessage());
        }catch (ClassGeneratorException $ex){
            die($ex->getMessage());
        }
        return true;
    }
    private function parseYamlClass() {
        $parser = new defaultYamlParser();
        $this->parsedFile = $parser->getParseFile($this->getDirYaml(),$this->getClassName());
    }
    
    private function generateClass($Yaml){
        $outputFile = "<?php\r\n";
        if (!isset($Yaml['main'])) die ("Main block is not define".__DIR__.__CLASS__."<br>".$this->getConfig());
        if (isset($Yaml['main']['namespace']) && ($Yaml['main']['namespace']!="")){
            $outputFile .= "namespace ".$Yaml['main']['namespace']."\r\n";
        }
        $outputFile .= $Yaml['main']['type']." ".$Yaml['main']['classname'];
        if (isset($Yaml['main']['extends']) && ($Yaml['main']['extends']!="")) {
            $outputFile .= " extends ".$Yaml['main']['extends'];
        }
        if (isset($Yaml['main']['interface']) && ($Yaml['main']['interface']!="")) {
            $outputFile .= " implements ".$Yaml['main']['interface'];
        }
        $outputFile .= " {"."\r\n";
        
        $vars = $Yaml['fields'];
        /*
         * appending fields to head class and after getters and setters
         */
        if (isset($key)) unset($key);
        foreach ($vars as $key){
            $outputFile .= $this->getVar($vars[$key]);
        }
        unset($vars);
        unset($key);
        
        foreach ($vars as $key){
            $outputFile .= $this->getSetter($vars[$key]);
            $outputFile .= $this->getGetter($vars[$key]);
        }
        
        $outputFile .= "}\r\n";
        
        return $outputFile;
    }

    public function getVar($var){
        return "\tprivate ".$var."= null;\r\n";
    }

    public function getGetter($fieldname){
        $str = "\tpublic function get".ucfirst($fieldname)."(\$value) {\r\n";
        $str .= "\t\t\$this->".$fieldname." = \$value;\r\n";
        $str .= "\t}\r\n";
    }
    
    public function getSetter($fieldname){
        $str = "\tpublic function set".ucfirst($fieldname)."() {\r\n";
        $str .= "\t\t\return $this->".$fieldname.";\r\n";
        $str .= "\t}\r\n";
    }
    
    private function writeClass($classes, $classname, $file){
        $path = (substr($classes, -1)=="/")?substr($classes, 0, strlen($classes)-1):$classes."/";
        $path .= $classname.".php";
        if (!file_put_contents($path, $file)){
            throw new ClassGeneratorException("File not write");
        }else{
            return true;
        }
    }

    public function generate() {
        
    }
}
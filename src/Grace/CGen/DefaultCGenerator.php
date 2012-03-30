<?php
/**
 * Description of DefaultCGenerator
 *
 * @author darthvader
 * @var $dirYaml default ../yaml - catalog with classes in Yaml
 * @var $classesDir default ../dirClasses - catalog with classes from Yaml
 * @var $className default ../yaml - classname
 * 
 */
namespace Grace\CGen;

class DefaultCGenerator extends AbstractCGenerator {
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
            file_put_contents($this->config["dirClasses"]."/".$this->config["className"].".php", $file);
        } catch (ErrorException $ex){
            //TODO add exceptions
            print_r($ex->getMessage());
            die();
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

    private function getVar($var){
        return "\tprivate ".$var."= null;\r\n";
    }

    private function getGetter($fieldname){
        //FIXME strtolower is so need?
        $str = "\tpublic function get".ucfirst(strtolower($fieldname))."(\$value) {\r\n";
        $str .= "\t\t\$this->".$fieldname." = \$value;\r\n";
        $str .= "\t}\r\n";
    }
    
    private function getSetter($fieldname){
        $str = "\tpublic function set".ucfirst(strtolower($fieldname))."() {\r\n";
        $str .= "\t\t\return $this->".$fieldname.";\r\n";
        $str .= "\t}\r\n";
    }

    
}

?>

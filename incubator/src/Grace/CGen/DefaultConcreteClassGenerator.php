<?php
namespace Grace\CGen;
/**
 * Description of DefaultConcreteClassGenerator
 * Translating YAML in format from exampleYamlClass.yml#2
 * if $classname is * then generate all YAML classes in directory
 * write class only if class with name $classname not exist yet
 * main method is "generate()" makes writing classes
 * @author gabushev@gmail.com
 * @return boolean true if all methods is fine, else return false
 */
class DefaultConcreteClassGenerator extends ClassGeneratorAbstract
{
    public $yaml = "";
    public $classes = "";
    public $classname = "";


    public function __construct($dirYaml, $classesDir, $className)
    {
        $this->config['dirYaml']    = $dirYaml;
        $this->config['dirClasses'] = $classesDir;
        $this->config['className']  = $className;
    }

    public function generate()
    {
        if ($this->getClassName() == "*") {
            $this->genManyYamlClass();
        } else {
            $this->genYamlClass();
        }
    }

    private function genManyYamlClass()
    {
        $this->parseYamlClass();
        foreach ($this->getParsedFile() as $key => $value) {
            $file = $this->generateClass($this->getParsedFile(), $key);
            try {
                if (!file_exists($this->getClassesDir() . "/" . $key . ".php")) {
                    $this->writeClass($this->getClassesDir(), $key, $file);
                }
            } catch (ErrorException $ex) {
                //TODO add exceptions
                //die($ex->getMessage());
                return false;
            } catch (ClassGeneratorException $ex) {
                return false;
                //die($ex->getMessage());
            }
        }
        return true;
    }

    private function genYamlClass()
    {
        $this->parseYamlClass();
        $file = $this->generateClass($this->getParsedFile(), $this->getClassName());
        try {
            if (!file_exists($this->getClassesDir() . "/" . $this->getClassName() . ".php")) {
                if ($this->writeClass($this->getClassesDir(), $this->getClassName(), $file)) {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (ErrorException $ex) {
            //TODO add exceptions
            die($ex->getMessage());
        } catch (ClassGeneratorException $ex) {
            die($ex->getMessage());
        }
        return true;
    }


    private function parseYamlClass()
    {
        $parser = new defaultYamlParser();
        $this->setParsedFile($parser->getParseFile($this->getDirYaml(), $this->getClassName()));
    }

    private function generateClass($Yaml, $class)
    {
        $outputFile = "<?php\n";
        $outputFile .= "abstract class " . $class;
        if (isset($Yaml[$class]['extends']) && ($Yaml[$class]['extends'] != "") && ($Yaml[$class]['extends'] != "none")
        ) {
            $outputFile .= " extends " . $Yaml[$class]['extends'];
        }
        $outputFile .= " {" . "\n";

        $vars = $Yaml[$class]['fields'];
        /*
         * appending fields to head class and after getters and setters
         */
        if (isset($key)) {
            unset($key);
        }
        foreach ($vars as $key) {
            $outputFile .= $this->getVar($vars[$key]);
        }
        unset($vars);
        unset($key);

        foreach ($vars as $key) {
            $outputFile .= $this->getSetter($vars[$key]);
            $outputFile .= $this->getGetter($vars[$key]);
        }

        $outputFile .= "}\n";

        return $outputFile;
    }

    private function writeClass($classes, $classname, $file)
    {
        $path = (substr($classes, -1) == "/") ? substr($classes, 0, strlen($classes) - 1) : $classes . "/";
        $path .= $classname . ".php";
        if (!file_put_contents($path, $file)) {
            throw new ClassGeneratorException("Error: file is not write");
        } else {
            return true;
        }
    }
}

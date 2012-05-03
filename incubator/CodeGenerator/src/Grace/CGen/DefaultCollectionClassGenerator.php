<?php
namespace Grace\CGen;

use ReflectionClass;
use ReflectionMethod;

if (!defined('GENERATE_NAMESPACE')) {
    define ('GENERATE_NAMESPACE', 'Grace\TestNamespace');
}
/**
 * Description of DefaultCollectionClassGenerator
 * @return boolean
 */
class DefaultCollectionClassGenerator extends ClassParserAbstract
{


    public function generate()
    {
        try {
            $fileClass = $this->getClassBody();
            if (!$this->writeClass($fileClass, $this->getOutputDir(), ucfirst($this->getClassName()))) {
                throw new ClassGeneratorException("Error! Could not write classfile!" . __FILE__ . __LINE__);
            }
            $fileClass = "";
            $fileClass = $this->getParentClassBody();
            if (!$this->writeClass($fileClass, $this->getOutputDir(), ucfirst($this->getClassName()) . "Abstract")) {
                throw new ClassGeneratorException("Error! Could not write classfile!" . __FILE__ . __LINE__);
            }
            return true;
        } catch (Grace\CGen\ClassGeneratorException $ex) {
            print_r($ex->getMessage());
        }
    }

    public function writeClass($fileContent, $path, $filename, $ext = ".php")
    {
        if (file_exists($path . "/" . $filename . $ext))
                {
                    return false;
                } //dont overwrite anything
        if (file_put_contents($path . "/" . $filename . $ext, $fileContent)) {
            return true;
        } else {
            return false;
        }
    }

    public function getClassBody()
    {
        $classBody = "<?php\n";
        $classBody .= "namespace " . GENERATE_NAMESPACE . "\n";
        $className = ucfirst($this->getClassName());
        $classBody .= "class " . $className . " extends " . $className . "Abstract {\n}";
        return $classBody;
    }

    public function getParentClassBody()
    {
        $classBody = "<?php\n";
        $classBody .= "namespace " . GENERATE_NAMESPACE . "\n";
        $className = ucfirst($this->getClassName()) . "Abstract";
        $classBody .= "class " . $className . " extends " . $this->getAdditionalClass() . "{\n";

        $dataClass = $this->getPreparedData();
        foreach ($dataClass["fields"] as $modifier => $fieldSet) {
            $arrayLen = count($dataClass["fields"][$modifier]);
            for ($i = 0; $i < $arrayLen; $i++) {
                $classBody .= $modifier . " " . $fieldSet[$i]->name . "\n";
            }
        }
        unset($modifier);
        unset ($fieldSet);

        $appendMethodBody = "(\$price, \$notifyClient = false) {\n
                        \tforeach (\$this as \$item) {\n
                            \t\$item->closeOrder(\$price, \$notifyClient);\n
                        \t}\n";

        foreach ($dataClass["methods"] as $modifier => $methodSet) {
            $arrayLen = count($dataClass["methods"][$modifier]);
            for ($i = 0; $i < $arrayLen; $i++) {
                //TODO add input vars
                $classBody .= $modifier . " " . $methodSet[$i]->name . "(";
                $args = $this->getMethodArgs($methodSet[$i]->name);
                if ($args) {
                    $classBody .= $args->str . ")";
                    //check for method name dont start with "get"
                    if ($args->isInsert) {
                        $classBody .= $appendMethodBody;
                    } else {
                        $classBody .= "){";
                    }
                }
                $args = null;
            }
        }
        unset($modifier);
        unset ($fieldSet);
        $classBody .= "};\n";
        return $classBody;
    }

    private function getMethodArgs($method)
    {
        $reflectionResult = $this->getOneMethodArgs($this->getLongParentClassName(), $method);
        if ($reflectionResult == NULL) {
            $reflectionResult = $this->getOneMethodArgs($this->getClassName(), $method);
        }
        /*
        try{
            $reflectionResult = $this->getOneMethodArgs($this->getParentClassName(), $method); 
            
        } catch (Grace\CGen\ClassGeneratorException $ex){
           $reflectionResult = $this->getOneMethodArgs($this->getClassName(), $method);
        }
         * 
         */

        if (count($reflectionResult) > 0) {
            $result = array();
            for ($i = 0; $i < count($reflectionResult); $i++) {
                array_push($result, "\$" . $reflectionResult[$i]->name);
            }
            $args->str = implode(", ", $result);
            if ((strpos(lcfirst($method), "get") === false)AND(strpos($method, "__") === false)) {
                $args->isInsert = TRUE;
            } else {
                $args->isInsert = FALSE;
            }
        } else {
            return false;
        }
    }

    private function getOneMethodArgs($className, $classMethod)
    {
        $reflection = new ReflectionClass($className);
        print_r($reflection->getEndLine());
        $method = $reflection->getMethods();
        $data   = new ReflectionMethod($reflection->getName(), $classMethod);
        if (count($data) > 0) {
            $return = array();
            for ($i = 0; $i < count($data); $i++) {
                $return[$i] = $data[$i]->name;
            }
            return $return;
        } else {
            return false;
        }
    }

    public function getPreparedData()
    {
        $methodsSelf   = $this->getItemsByMod("getClassMethods");
        $methodsParent = $this->getItemsByMod("getParentClassMethods");
        $fieldsSelf    = $this->getItemsByMod("getClassFields");
        $fieldsParent  = $this->getItemsByMod("getParentClassFields");
        $result        = array(
            "methods" => array(
                "public"    => array(),
                "private"   => array(),
                "protected" => array(),
                "static"    => array()
            ),
            "fields"  => array(
                "public"    => array(),
                "private"   => array(),
                "protected" => array(),
                "static"    => array()
            )
        );

        //TODO Delete duplicate methods and fields - diff classes?

        foreach ($methodsSelf as $key => $value) {
            foreach ($methodsSelf[$key] as $methodId => $method) {
                array_push($result["methods"][$key], $method);
            }
        }
        unset($key);
        unset($value);
        unset($methodId);
        unset($method);
        /*
        foreach ($methodsParent as $key => $value){
            foreach ($methodsParent[$key] as $methodId => $method){
                if (!array_key_exists($method->name, $result["methods"][$key])){
                    array_push($result["methods"][$key], $method);
                }
            }
        }
        unset($key);
        unset($value);
        unset($methodId);
        unset($method);
        */
        foreach ($fieldsSelf as $key => $value) {
            foreach ($fieldsSelf[$key] as $methodId => $method) {
                array_push($result["fields"][$key], $method);
            }
        }
        unset($key);
        unset($value);
        unset($methodId);
        unset($method);

        foreach ($fieldsParent as $key => $value) {
            foreach ($fieldsParent[$key] as $methodId => $method) {
                array_push($result["fields"][$key], $method);
            }
        }
        unset($key);
        unset($value);
        unset($methodId);
        unset($method);

        return $result;
    }

    public function getItemsByMod($fName)
    {
        $methods    = array(
            "public"    => array(),
            "protected" => array(),
            "private"   => array(),
            "static"    => array()
        );
        $types      = array("public", "protected", "private", "static");
        $countTypes = count($types);
        for ($i = 0; $i < $countTypes; $i++) {
            $methods[$types[$i]] = $this->$fName($types[$i]);
        }

        return $methods;
    }
}
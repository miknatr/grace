<?php

namespace Grace\Generator;

/**
 * Generator conventions:
 * Autoload - PSR-0
 * New line - "\n"
 * Intent - 4 spaces
 * "{" on new line in methods and classes
 */
class ClassGenerator
{
    private $classFlags = '';
    private $className = '';
    private $classExtends = '';
    private $classImplements = array();
    private $classDocTags = array();
    private $usages = array();
    private $constants = array();
    private $properties = array();
    private $methods = array();

    public function __construct($flags, $className, $extends = '', array $implements = array(), array $docTags = array())
    {
        $this->classFlags      = $flags;
        $this->className       = $className;
        $this->classExtends    = $extends;
        $this->classImplements = $implements;
        $this->classDocTags    = $docTags;
    }
    public function addUse($use)
    {
        $this->usages[] = $use;
    }
    public function addConstant($name, $value, array $docTags = array())
    {
        $this->constants[] = array(
            'name'    => $name,
            'value' => $value,
            'docTags' => $docTags,
        );
    }
    public function addProperty($flags, $name, $value, array $docTags = array())
    {
        $this->properties[] = array(
            'flags'   => $flags,
            'name'    => $name,
            'value'   => $value,
            'docTags' => $docTags,
        );

    }
    public function addMethod($flags, $name, array $parametersString, $code, array $docTags = array())
    {
        $this->methods[] = array(
            'flags'      => $flags,
            'name'       => $name,
            'parameters' => $parametersString,
            'code'       => $code,
            'docTags'    => $docTags,
        );

    }
    public function getFileContent()
    {
        $namepaces = explode('\\', $this->className);
        $class = $namepaces[count($namepaces) - 1];
        unset($namepaces[count($namepaces) - 1]);
        $namespace = implode('\\', $namepaces);
        $namespaceString = $namespace == '' ? '' : "namespace $namespace";


        $usagesString = array_reduce($this->usages, function(&$result, $item) { return $result .= "use $item;\n"; }, '');


        $classDocString = self::makeDocTags($this->classDocTags);


        $classString = "class $class"
            . ($this->classExtends == '' ? '' : " extends $this->classExtends")
            . (count($this->classImplements) == 0 ? '' : " implements " . implode(', ', $this->classImplements));


        $constantsString = "";
        foreach ($this->constants as $constant) {
            $constantsString .= self::makeDocTags($constant['docTags'])
                . "const {$constant['name']} = " . self::makeValue($constant['value']) . ";\n";
        }


        $propertiesString = "";
        foreach ($this->properties as $property) {
            $value = self::makeValue($property['value']);
            $valueString = $value != '' ? ' = ' . $value : '';
            $propertiesString .= self::makeDocTags($property['docTags'])
                . self::makeFlags($property['flags'], 'property') . "\${$property['name']}$valueString;\n";
        }


        $methodsString = "";
        foreach ($this->methods as $method) {
            $codeString = self::intent($method['code']);
            $methodsString .= self::makeDocTags($method['docTags'])
                . self::makeFlags($method['flags'], 'property') . "\${$method['name']}({$method['parameters']})"
                . ($codeString == '' ? ";\n" : "\n{\n$codeString\n}\n");
        }


        return "<?php\n\n"
            . "$namespaceString\n\n"
            . "$usagesString\n\n"
            . "$classDocString\n"
            . "$classString\n"
            . "{\n"
            . self::intent($constantsString) . "\n"
            . self::intent($propertiesString) . "\n"
            . self::intent($methodsString) . "\n"
            . "}\n"
            ;
    }
    public function write($baseDir)
    {
        $namepaces = explode('\\', $this->className);
        $class = $namepaces[count($namepaces) - 1];
        unset($namepaces[count($namepaces) - 1]);

        $dir = $baseDir . '/' . implode('/', $namepaces);
        if (is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($dir . '/' . $class . '.php', $this->getFileContent());
    }


    //GENERATION HELPERS
    static private function intent($code, $num = 1)
    {
        return str_replace("\n", "\n" . str_repeat('    ', $num), $code);
    }
    static private function makeDocTags(array $docTags)
    {
        if (count($docTags) == 0) {
            return '';
        }

        return "/**\n" . array_reduce($docTags, function (&$result, $item) { return $result .= " * $item;\n"; }, '') . " */\n";
    }

    static private function makeValue($value, $intentNum = 1)
    {
        $type = gettype($value);

        switch ($type) {
            case 'boolean':
                return ($value ? 'true' : 'false');
            case 'integer':
            case 'double':
                return $value;
            case 'string':
                return "'" . addcslashes($value, "'") . "'";
            case 'NULL':
                return 'null';
            case 'array':
                $r = "array(\n";

                foreach ($value as $k => $v) {
                    $r .= self::intent((is_int($k) ? $k : "'" . addcslashes($k, "'") . "'") . ' => ' . self::makeValue($v, $intentNum + 1) . ",\n", $intentNum);
                }

                $r .= "\n)";

                return $r;
            case 'object':
            case 'resource':
            case 'unknown type':
            default:
                throw new \LogicException("Type '" . get_class($value)."' is cannot be used as default value.");
        }
    }
    static private function makeFlags($flags, $type)
    {
        if ($flags == '') {
            return '';
        }

        return implode(' ', self::parseFlags($flags, $type)) . ' ';
    }


    //WORK WITH FLAGS
    static private $flags = array(
        'a' => 'abstract',
        's' => 'static',
        'f' => 'final',
        '-' => 'private',
        '=' => 'protected',
        '+' => 'public',
    );
    static private $typeFlags = array(
        'class'    => 'fa',
        'method'   => 'f+=-sa',
        'property' => '+=-s',
        'constant' => '',
    );
    static private function parseFlags($flagsString, $type)
    {
        if (!isset(self::$typeFlags[$type])) {
            throw new \LogicException('Unsupported type');
        }

        $flags = array();

        for ($i = 0; $i < strlen($flagsString); $i++) {
            if (strpos($flagsString, self::$typeFlags[$type][$i]) !== false) {
                $flags[] = self::$flags[self::$typeFlags[$type][$i]];
            }
        }

        return $flags;
    }
}

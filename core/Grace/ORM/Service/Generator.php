<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Grace\ORM\Service;

use Grace\ORM\Service\Config\Config;
use Grace\ORM\Service\ClassNameProvider;

class Generator
{
    /** @var Config */
    private $modelsConfig;
    /** @var ClassNameProvider */
    private $classNameProvider;
    /** @var TypeConverter */
    private $typeConverter;

    private $baseDir;
    private $graceClass;
    private $baseGraceClass;
    private $baseModelClass;

    private $isDryRun = false;
    /** @var callable */
    private $logger;

    // we use this to make error messages more detailed
    private $lastProcessedElement = '';

    const INLINE_GENCODE_MARKER = 'BEGIN GRACE GENERATED CODE';

    public function __construct(Config $modelsConfig, TypeConverter $typeConverter, ClassNameProvider $classNameProvider, $baseDir, $graceClass, $baseGraceClass = '\\Grace\\ORM\\Grace', $baseModelClass = '\\Grace\\ORM\\ModelAbstract')
    {
        $this->modelsConfig      = $modelsConfig;
        $this->classNameProvider = $classNameProvider;
        $this->baseDir           = rtrim($baseDir, '\\/');
        $this->graceClass        = '\\' . ltrim($graceClass, '\\');
        $this->baseGraceClass    = '\\' . ltrim($baseGraceClass, '\\');
        $this->typeConverter     = $typeConverter;
        $this->baseModelClass    = $baseModelClass;
    }

    public function generate($dryRun = false, callable $logger = null)
    {
        $this->logger = $logger;

        // there are no other public methods
        // so we can do this! yay!
        $this->isDryRun = $dryRun;

        try {
            $this->addPhpdocToClass(
                $this->getClassFilename($this->graceClass, $this->baseGraceClass),
                $this->generateGraceClassPhpdoc()
            );

            foreach ($this->modelsConfig->models as $modelName => $config) {
                $modelClass = $this->classNameProvider->getModelClass($modelName);
                $modelClassFilename = $this->getClassFilename($modelClass, $this->baseModelClass);

                // PHPDOC
                $this->addPhpdocToClass(
                    $modelClassFilename,
                    $this->generateModelClassPhpdoc($modelName)
                );

                $finderClass = $this->classNameProvider->getFinderClass($modelName);
                $this->addPhpdocToClass(
                    $this->getClassFilename($finderClass, '\\Grace\\ORM\\FinderAbstract'),
                    $this->generateFinderClassPhpdoc($modelName)
                );

                $sbClass = $this->classNameProvider->getSelectBuilderClass($modelName);
                $this->addPhpdocToClass(
                    $this->getClassFilename($sbClass, '\\Grace\\SQLBuilder\\SelectBuilder'),
                    $this->generateSelectBuilderClassPhpdoc($modelName)
                );

                // METHODS
                $this->addMethodsToClass(
                    $modelClassFilename,
                    $this->generateModelClassMethods($modelName)
                );
            }
        } catch (\Exception $e) {
            throw new \Exception("Error while processing {$this->lastProcessedElement}: " . $e->getMessage(), $e->getCode(), $e);
        }
    }


    //
    // PHPDOC GENERATION
    //

    private function generateSelectBuilderClassPhpdoc($modelName)
    {
        $this->lastProcessedElement = $modelName;

        $modelClass = $this->classNameProvider->getModelClass($modelName);

        return array(
            "@method {$modelClass} fetchOneOrFalse()",
            "@method {$modelClass}[] fetchAll()",
        );
    }

    private function generateModelClassPhpdoc($modelName)
    {
        $this->lastProcessedElement = $modelName;

        $modelClass = $this->classNameProvider->getModelClass($modelName);

        return array(
            "@property {$this->graceClass} \$orm",
            "@method $modelClass getOriginalModel()",
        );
    }

    private function generateModelClassMethods($modelName)
    {
        $methods = array();

        foreach ($this->modelsConfig->models[$modelName]->properties as $propName => $propConfig) {
            $name = ucfirst($propName);

            $this->lastProcessedElement = $modelName . '.' . $propName;

            if ($propName == 'id') {
                continue;
            }

            $type       = $this->typeConverter->getPhpType($propConfig->type);
            $setterType = $this->typeConverter->getSetterPhpdocType($propConfig->type);

            $methods['force']['get' . $name] = $this->unindent(3, "
                /**
                 * @return {$type}
                 */
                final public function get{$name}()
                {
                    return \$this->properties['{$propName}'];
                }
            ");

            if ($propConfig->isSettable) {
                $methods['optional']['set' . $name] = $this->unindent(4, "
                    /**
                     * @param {$setterType} \${$propName}
                     * @return \$this
                     */
                    public function set{$name}(\${$propName})
                    {
                        return \$this->setProperty('{$propName}', \${$propName});
                    }
                ");
            }

            if ($propConfig->resolvesToModelName) {
                $getterName = 'get' . ucfirst(substr($propName, 0, -2)); // removing Id suffix: regionId => getRegion
                $foreignClass = $this->classNameProvider->getModelClass($propConfig->resolvesToModelName);
                $finderProperty = lcfirst($propConfig->resolvesToModelName) . 'Finder';

                $methods['force'][$getterName] = $this->unindent(4, "
                    /**
                     * @return {$foreignClass}
                     */
                    final public function {$getterName}()
                    {
                        return \$this->orm->{$finderProperty}->getByIdOrFalse(\$this->getProperty('{$propName}'));
                    }
                ");
            }
        }


        // DB TO PHP CONVERSION

        $dbToPhpMethodBody = '';
        foreach ($this->modelsConfig->models[$modelName]->properties as $propName => $propConfig) {
            $this->lastProcessedElement = $modelName . '.' . $propName;

            $propertyCode = $this->typeConverter->getDbToPhpConverterCode($propConfig->type);

            if ($propConfig->isNullable) {
                $dbToPhpMethodBody .= $this->unindent(1, "
                    // {$propName}
                    \$value = \$dbArray['{$propName}'];
                    \$this->properties['{$propName}'] = (\$value === null) ? null : ({$propertyCode});
                ");
            } else {
                $dbToPhpMethodBody .= $this->unindent(1, "
                    // {$propName}
                    \$value = \$dbArray['{$propName}'];
                    if (\$value === null) {
                        throw new \\Grace\\ORM\\Type\\ConversionImpossibleException('Null is not allowed in {$modelName}.{$propName}');
                    }
                    \$this->properties['{$propName}'] = {$propertyCode};
                ");
            }
        }

        $methods['force']['setPropertiesFromDbArray'] = $this->unindent(2, "
            final protected function setPropertiesFromDbArray(array \$dbArray)
            {
                {$dbToPhpMethodBody}
            }
        ");


        // INITIAL PROPERTY VALUES

        $initPropsMethodBody = '';
        foreach ($this->modelsConfig->models[$modelName]->properties as $propName => $propConfig) {
            $type = $propConfig->type;

            if ($propConfig->default) {
                $valueDef = $propConfig->default;
                $rawValueCode = ($valueDef == 'now') ? '\\Grace\\ORM\\Type\\TypeTimestamp::format(time())' : var_export($valueDef, true);
                $isNullAllowed = $propConfig->isNullable;
                // TODO убрать необходимость в этом вызове
                $valueCode = '$this->orm->typeConverter->convertOnSetter('
                    . var_export($type, true) . ', '
                    . $rawValueCode . ', '
                    . var_export($isNullAllowed, true)
                    . ')';
            } else if ($propConfig->isNullable) {
                $valueCode = 'null';
            } else {
                $valueCode = $this->typeConverter->getPhpDefaultValueCode($type);
            }

            $initPropsMethodBody .= "\n                    '{$propName}' => {$valueCode},";
        }
        $initPropsMethodBody .= "\n                ";

        $methods['force']['setDefaultPropertyValues'] = $this->unindent(2, "
            final protected function setDefaultPropertyValues()
            {
                \$this->properties = array({$initPropsMethodBody});
            }
        ");

        return $methods;
    }

    private function unindent($indentLevel, $codeBlock)
    {
        $prefix = str_repeat('    ', $indentLevel);
        return preg_replace('/^' . preg_quote($prefix) . '/m', '', $codeBlock);
    }

    private function generateFinderClassPhpdoc($modelName)
    {
        $this->lastProcessedElement = $modelName;

        $modelClass = $this->classNameProvider->getModelClass($modelName);
        $sbClass    = $this->classNameProvider->getSelectBuilderClass($modelName);

        return array(
            "@property {$this->graceClass} \$orm",
            "@method {$modelClass} fetchOneOrFalse()",
            "@method {$modelClass} getByIdOrFalse(\$id)",
            "@method {$modelClass} create(array \$properties = array())",
            "@method {$modelClass}[] fetchAll()",
            "@method {$sbClass} getSelectBuilder()",
        );
    }

    private function generateGraceClassPhpdoc()
    {
        $phpdoc = array();
        foreach ($this->modelsConfig->models as $name => $config) {
            $this->lastProcessedElement = $name;

            // example:
            //  * @property \Grace\Bundle\Finder\TaxiPassengerFinder $taxiPassengerFinder
            $propName = lcfirst($name);
            $phpdoc[] = "@property " . $this->classNameProvider->getFinderClass($name) . " \${$propName}Finder";
        }
        return $phpdoc;
    }


    //
    // FILE CONTENT MANIPULATION
    //

    private function writeFile($filename, $contents)
    {
        $oldContents = file_exists($filename) ? file_get_contents($filename) : null;

        if (!$this->isDryRun) {
            file_put_contents($filename, $contents);
        }

        $trimmedFilename = substr($filename, strlen($this->baseDir) + 1);

        if ($oldContents === null) {
            $this->log("Created $trimmedFilename");
        } elseif ($oldContents !== $contents) {
            $this->log("Touched $trimmedFilename");
        }
    }

    private function getClassFilename($class, $parentClass)
    {
        $filename = $this->baseDir . '/' . str_replace('\\', '/', ltrim($class, '\\')) . '.php';

        if (!file_exists($filename)) {
            preg_match('#^\\\\(.*)\\\\([^\\\\]+)$#', $class, $match);
            $classNs   = $match[1];
            $className = $match[2];

            preg_match('#^\\\\(.*\\\\([^\\\\]+))$#', $parentClass, $match);
            $parentNs   = $match[1];
            $parentName = $match[2];

            $contents = "<?php\n\nnamespace {$classNs};\n\nuse {$parentNs};\n\nclass {$className} extends {$parentName}\n{\n}\n";
            $this->writeFile($filename, $contents);
        }

        return $filename;
    }

    /**
     * $methods = array(
     *     'optional' => array(...methodName => methodCode...),
     *     'force'    => array(...methodName => methodCode...),
     * )
     *
     * @param $filename
     * @param array $methods
     * @throws \LogicException
     */
    private function addMethodsToClass($filename, array $methods)
    {
        $contents = file_get_contents($filename);

        $markerBlock = "    //\n    // ".static::INLINE_GENCODE_MARKER."\n    //\n\n    // <editor-fold defaultstate=\"collapsed\" desc=\"Wall of text\">\n";

        // removing previously generated methods
        $pos = strpos($contents, $markerBlock);
        if ($pos !== false) {
            $contents = substr($contents, 0, $pos);
            $contents = preg_replace("/\n*$/", '', $contents);
            $contents .= "\n}\n";
        }

        if (!isset($methods['force'])) {
            $methods['force'] = array();
        }

        if (!isset($methods['optional'])) {
            $methods['optional'] = array();
        }

        // filtering methods that are already defined
        foreach ($methods['force'] as $name => $codeBlock) {
            if (preg_match('/^\s*([a-z]+\s+)*function\s+' . preg_quote($name, '/') . '\s*\(/m', $contents)) {
                throw new \LogicException('You cannot override getters because fuck you (' . $name . '() in ' . $filename . ')');
            }
        }

        // filtering methods that are already defined
        foreach ($methods['optional'] as $name => $codeBlock) {
            if (preg_match('/^\s*([a-z]+\s+)*function\s+' . preg_quote($name, '/') . '\s*\(/m', $contents)) {
                unset($methods['optional'][$name]);
            }
        }

        $methods = array_merge($methods['optional'], $methods['force']);

        // we want to sort the generated methods because
        // 1. this will make it easier to find methods in the file
        // 2. there will be no extra diff in commits due to random movement of methods
        uksort($methods, function ($a, $b) {
            // we want setters to be text to getters
            // so the order will be: getA() setA() getB() setB()
            $aName = preg_replace('/^[sg]et/', '', $a);
            $bName = preg_replace('/^[sg]et/', '', $b);
            $cmp = strcasecmp($aName, $bName);
            return ($cmp == 0) ? strcasecmp($a, $b) : $cmp;
        });

        $methodsCode = join('', $methods);
        // removing empty lines inside {}
        $methodsCode = preg_replace('/\{\n( *\n)+/', "{\n", $methodsCode);
        $methodsCode = preg_replace('/(\n *)+(\n *\})/', "$2", $methodsCode);

        // inserting methods into the file
        $contents = preg_replace('/\}\s*$/', '', $contents);
        $contents .= "\n\n" . $markerBlock;
        $contents .= $methodsCode;
        $contents .= "\n    // </editor-fold>\n}\n";

        $this->writeFile($filename, $contents);
    }

    /**
     * @param string $filename
     * @param string[] $phpdocLines lines of the phpdoc without leading stars (like '@property string $blah')
     */
    private function addPhpdocToClass($filename, array $phpdocLines)
    {
        $contents = file_get_contents($filename);

        $marker = ' * ' . self::INLINE_GENCODE_MARKER;

        // we want to sort the generated phpdoc because
        // 1. this will make it easier to find methods/properties in the file
        // 2. there will be no extra diff in commits due to random movement of phpdoc lines
        usort($phpdocLines, function ($a, $b) {
            $re = '/^(@[a-z]+)\s+\S+([gs]et)?(.*)$/';
            if (preg_match($re, $a, $matchA) && preg_match($re, $b, $matchB)) {
                // properties go before methods
                $aProp = ($matchA[1] == '@property') ? 1 : 0;
                $bProp = ($matchA[1] == '@property') ? 1 : 0;
                if ($aProp xor $bProp) {
                    return $aProp - $bProp;
                }

                // getters are kept next to setters
                $cmp = strcasecmp($matchA[3], $matchB[3]);
                if ($cmp != 0) {
                    return $cmp;
                }
                // fallback to default sorting by the whole string
            }
            return strcasecmp($a, $b);
        });
        $phpdocBody = empty($phpdocLines) ? '' : ' * ' . join("\n * ", $phpdocLines) . "\n";

        $doRemoveLines = false;

        $lines = explode("\n", $contents);
        foreach ($lines as $i => $line) {
            if (preg_match('/^(\s*[a-z]+)*\s*class\s/', $line)) {
                // found class declaration
                // this means there is no phpdoc
                $lines[$i] = "/**\n$marker\n" . $phpdocBody . " */\n" . $line;
                break;
            }

            if ($line == $marker) {
                // found marker
                // this means we have a generated phpdoc in the file
                // we need to remove any lines below it until we find the end of the phpdoc
                $doRemoveLines = true;
            }

            if ($line == ' */') {
                // found the end of phpdoc
                // we know that there is nothing generated in the file already
                // so we can safely paste newly generated phpdoc here before the end of the phpdoc block
                $lines[$i] = "$marker\n" . $phpdocBody . $line;
                if (!$doRemoveLines) {
                    // no lines were removed
                    // this means there was no generated block in the file, but some phpdoc was there
                    // in this case, we add a newline after existing phpdoc
                    $lines[$i] = "\n" . $lines[$i];
                }
                break;
            }

            if ($doRemoveLines) {
                // we're between our marker and the end of phpdoc block
                // everything here needs to be removed
                unset($lines[$i]);
            }
        }

        $contents = join("\n", $lines);
        $this->writeFile($filename, $contents);
    }


    //
    // MISC
    //

    private function log($message)
    {
        if ($this->logger) {
            call_user_func($this->logger, $message);
        }
    }
}

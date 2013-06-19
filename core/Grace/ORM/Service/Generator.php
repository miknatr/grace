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
use Symfony\Component\Yaml\Yaml;

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

    private $isDryRun = false;
    /** @var callable */
    private $logger;

    const INLINE_GENCODE_MARKER = 'BEGIN GRACE GENERATED CODE';

    public function __construct(Config $modelsConfig, TypeConverter $typeConverter, ClassNameProvider $classNameProvider, $baseDir, $graceClass, $baseGraceClass = '\\Grace\\ORM\\Grace')
    {
        $this->modelsConfig      = $modelsConfig;
        $this->classNameProvider = $classNameProvider;
        $this->baseDir           = rtrim($baseDir, '\\/');
        $this->graceClass        = '\\' . ltrim($graceClass, '\\');
        $this->baseGraceClass    = '\\' . ltrim($baseGraceClass, '\\');
        $this->typeConverter     = $typeConverter;
    }

    public function generate($dryRun = false, callable $logger = null)
    {
        $this->logger = $logger;

        // there are no other public methods
        // so we can do this! yay!
        $this->isDryRun = $dryRun;

        $this->addPhpdocToClass(
            $this->getClassFilename($this->graceClass, $this->baseGraceClass),
            $this->generateGraceClassPhpdoc()
        );

        foreach ($this->modelsConfig->models as $modelName => $config) {
            $modelClass = $this->classNameProvider->getModelClass($modelName);
            $this->addPhpdocToClass(
                $this->getClassFilename($modelClass, '\\Grace\\ORM\\ModelAbstract'),
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
        }
    }


    //
    // PHPDOC GENERATION
    //

    private function generateSelectBuilderClassPhpdoc($modelName)
    {
        $modelClass = $this->classNameProvider->getModelClass($modelName);

        $phpdoc = '';
        $phpdoc .= " * @method {$modelClass} fetchOneOrFalse()\n";
        $phpdoc .= " * @method {$modelClass}[] fetchAll()\n";
        return $phpdoc;
    }

    private function generateModelClassPhpdoc($modelName)
    {
        $modelClass = $this->classNameProvider->getModelClass($modelName);

        $phpdoc = '';

        $phpdoc .= " * @property {$this->graceClass} \$orm\n";

        foreach ($this->modelsConfig->models[$modelName]->properties as $propName => $propConfig) {
            $name = ucfirst($propName);

            if (!method_exists($modelClass, "get{$name}")) {
                if ($propConfig->mapping->localPropertyType) {
                    $type = $this->typeConverter->getPhpType($propConfig->mapping->localPropertyType);
                } elseif ($propConfig->mapping->relationLocalProperty) {
                    $parentName = $this->modelsConfig->models[$modelName]->parents[$propConfig->mapping->relationLocalProperty]->parentModel;
                    $typeAlias = $this->modelsConfig->models[$parentName]->properties[$propConfig->mapping->relationForeignProperty]->mapping->localPropertyType;
                    $type = $this->typeConverter->getPhpType($typeAlias);
                } else {
                    throw new \LogicException("Bad mapping in $modelName:$propName");
                }

                $phpdoc .= " * @method $type get{$name}()\n";
            }

            if ($propConfig->mapping->localPropertyType and $propName != 'id' and !method_exists($modelClass, "set{$name}")) {
                $phpdoc .= " * @method {$modelClass} set{$name}(\$$propName)\n";
            }
        }

        foreach ($this->modelsConfig->models[$modelName]->parents as $propName => $parentConfig) {
            $name = ucfirst(substr($propName, 0, -2)); // removing Id suffix
            $parentClass = $this->classNameProvider->getModelClass($parentConfig->parentModel);

            if (!method_exists($modelClass, "get{$name}")) {
                $phpdoc .= " * @method {$parentClass} get{$name}()\n";
            }
        }

        return $phpdoc;
    }

    private function generateFinderClassPhpdoc($modelName)
    {
        $modelClass = $this->classNameProvider->getModelClass($modelName);
        $sbClass    = $this->classNameProvider->getSelectBuilderClass($modelName);

        $phpdoc = '';
        $phpdoc .= " * @method {$modelClass} fetchOneOrFalse()\n";
        $phpdoc .= " * @method {$modelClass} getByIdOrFalse(\$id)\n";
        $phpdoc .= " * @method {$modelClass} create(array \$properties = array())\n";
        $phpdoc .= " * @method {$modelClass}[] fetchAll()\n";
        $phpdoc .= " * @method {$sbClass} getSelectBuilder()\n";

        return $phpdoc;
    }

    private function generateGraceClassPhpdoc()
    {
        $phpdoc = '';
        foreach ($this->modelsConfig->models as $name => $config) {
            // example:
            //  * @property \Grace\Bundle\Finder\TaxiPassengerFinder $taxiPassengerFinder
            $propName = lcfirst($name);
            $phpdoc .= " * @property " . $this->classNameProvider->getFinderClass($name) . " \${$propName}Finder\n";
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

        if ($oldContents === null) {
            $this->log("Created $filename");
        } elseif ($oldContents !== $contents) {
            $this->log("Touched $filename");
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
     * @param string $filename
     * @param string $phpdocBody body of the phpdoc with leading stars, but without start/end boundaries (/**)
     */
    private function addPhpdocToClass($filename, $phpdocBody)
    {
        $contents = file_get_contents($filename);

        $marker = ' * ' . self::INLINE_GENCODE_MARKER;

        $fileNamespace = '';
        $importedClasses = array();
        $namespaceLineIndex = false;
        $lastUseLineIndex = false;
        $doRemoveLines = false;

        $lines = explode("\n", $contents);
        foreach ($lines as $i => $line) {
            if (preg_match('/^namespace (\S+);/', $line, $match)) {
                // found namespace declaration
                $namespaceLineIndex = $i;
                $fileNamespace = $match[1];
            }

            if (preg_match('/^use (\S+)(?:\s+as\s+(\S+))?;/i', $line, $match)) {
                // found use declaration
                $lastUseLineIndex = $i;
                if (empty($match[2])) {
                    list(, $shortName) = $this->splitClassName($match[1]);
                    $importedClasses[$shortName] = $match[1];
                } else {
                    $importedClasses[$match[2]] = $match[1];
                }
            }

            if (preg_match('/^(\s*[a-z]+)*\s*class\s/', $line)) {
                // found class declaration
                // this means there is no phpdoc
//                $newImports = $this->simplifyClassNames($phpdocBody, $fileNamespace, $importedClasses, $filename);
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
//                $newImports = $this->simplifyClassNames($phpdocBody, $fileNamespace, $importedClasses, $filename);
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

        // adding new imports if there were any
        if (!empty($newImports)) {
            if ($lastUseLineIndex === false) {
                $lastUseLineIndex = $namespaceLineIndex;
                $lines[$lastUseLineIndex] .= "\n";
            }

            foreach ($newImports as $alias => $fqn) {
                list(, $shortName) = $this->splitClassName($fqn);
                $fqn = substr($fqn, 1); // removing leading slash
                if ($shortName == $alias) {
                    $lines[$lastUseLineIndex] .= "\nuse {$fqn};";
                } else {
                    $lines[$lastUseLineIndex] .= "\nuse {$fqn} as {$alias};";
                }
            }
        }

        $contents = join("\n", $lines);
        $this->writeFile($filename, $contents);
    }

    /**
     * Converts FQN to short class names and imports
     *
     * This is disabled for now because with simplified class names PhpStorm (as of PS 129.757)
     * cannot properly resolve class members. So we use FQNs and disable the related inspection in the IDE.
     *
     * @param string $phpdocBody
     * @param string $fileNamespace
     * @param string[] $importedClasses
     * @param string $filename
     * @return string[] imports to add
     */
    private function simplifyClassNames(&$phpdocBody, $fileNamespace, $importedClasses, $filename)
    {
        $newImports = array();

        $phpdocBody = preg_replace_callback(
            '/\\\\[a-zA-Z0-9_\\\\]+/',
            function ($match) use ($importedClasses, $fileNamespace, &$newImports, $filename) {
                $fqn = $match[0];
                list($ns, $shortName) = $this->splitClassName($fqn);

                if ($ns == $fileNamespace) {
                    return $shortName;
                }

                if (!isset($importedClasses[$shortName])) {
                    $newImports[$shortName] = $fqn;
                    return $shortName;
                }

                if ($importedClasses[$shortName] == substr($fqn, 1)) {
                    return $shortName;
                }

                // there is an import of other class with the same name
                throw new \LogicException("Please remove or rename the import of class {$shortName} from {$filename}");
            },
            $phpdocBody
        );

        return $newImports;
    }


    //
    // MISC
    //

    /**
     * @param string $className absolute name starting with \
     * @return array
     */
    private function splitClassName($className)
    {
        $pos = strrpos($className, '\\');
        if ($pos === false) {
            return array('', $className);
        }
        return array(substr($className, 1, $pos - 1), substr($className, $pos + 1));
    }

    private function log($message)
    {
        if ($this->logger) {
            call_user_func($this->logger, $message);
        }
    }
}

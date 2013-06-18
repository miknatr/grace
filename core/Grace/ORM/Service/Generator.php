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
        $shortModelClass = $modelName; //in Your\App\Model\ namespace
        $modelClass = $this->classNameProvider->getModelClass($modelName);

        $phpdoc = '';

        $phpdoc .= " * @property {$this->graceClass} \$orm\n";

        foreach ($this->modelsConfig->models[$modelName]->properties as $propName => $propConfig) {
            $name = ucfirst($propName);

            if (!method_exists($modelClass, "get{$name}")) {
                $phpdoc .= " * @method mixed get{$name}()\n";
            }

            if ($propConfig->mapping->localPropertyType and $propName != 'id' and !method_exists($modelClass, "set{$name}")) {
                $phpdoc .= " * @method {$shortModelClass} set{$name}(\$$propName)\n";
            }
        }

        foreach ($this->modelsConfig->models[$modelName]->parents as $propName => $parentConfig) {
            $name = ucfirst(substr($propName, 0, -2)); // removing Id suffix
            $shortParentClass = $parentConfig->parentModel; //in Your\App\Model\ namespace

            if (!method_exists($modelClass, "get{$name}")) {
                $phpdoc .= " * @method {$shortParentClass} get{$name}()\n";
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
            $name = lcfirst($name);
            $phpdoc .= " * @property " . $this->classNameProvider->getFinderClass($name) . " \${$name}Finder\n";
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

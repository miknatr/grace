<?php

namespace Grace\Generator;

use Grace\Generator\ZendCodeGenerator\PhpDoc;
use Grace\Generator\GeneratorPlugin\PluginAbstract;
use Symfony\Component\Yaml\Yaml;

class ModelsGenerator
{
    const CONFIG_CONNECTION = 'connection';
    const CONFIG_PROPERTIES = 'properties';
    const CONFIG_PARENTS    = 'parents';
    const FORM_LABEL        = 'form_label';
    const CACHE_DIRECTORY   = 'grace';

    const BASE_CLASS_MANAGER        = '\\Grace\\ORM\\ManagerAbstract';
    const BASE_CLASS_RECORD         = '\\Grace\\ORM\\Record';
    const BASE_CLASS_FINDER_SQL     = '\\Grace\\ORM\\FinderSql';
    const BASE_CLASS_FINDER_CRUD    = '\\Grace\\ORM\\FinderCrud';
    const BASE_CLASS_COLLECTION     = '\\Grace\\ORM\\Collection';
    const CONCRETE_CLASS_MANAGER    = 'ORMManager';
    const MAPPER_FIELDS_ARRAY       = 'fieldNames';
    const MAPPER_NO_DB_FIELDS_ARRAY = 'noDbFieldNames';

    private $modelConfigResources;
    private $namespace;
    private $containerClass;
    private $validatorsFile;
    private $realClassDirectory;
    private $extraPlugins;

    private $versionFilename;

    private $outputDir = '';
    private $abstractClassDirectory = '';
    private $defaultPlugins = array(
        '\\Grace\\Generator\\GeneratorPlugin\\ParentsPlugin',
        '\\Grace\\Generator\\GeneratorPlugin\\FilterPlugin',
        '\\Grace\\Generator\\GeneratorPlugin\\CallSubObjectGetterPlugin',
        '\\Grace\\Generator\\GeneratorPlugin\\FilteredFieldByFunctionPlugin',
        '\\Grace\\Generator\\GeneratorPlugin\\SubObjectPlugin',
        '\\Grace\\Generator\\GeneratorPlugin\\FormDataPlugin',
    );
    /**
     * @var PluginAbstract[]
     */
    private $plugins = array();

    public function __construct(array $modelConfigResources, $namespace, $containerClass, $validatorsFile, $realClassDirectory, $abstractClassDirectory, array $extraPlugins)
    {
        $this->modelConfigResources   = $modelConfigResources;
        $this->namespace              = $namespace;
        $this->containerClass         = $containerClass;
        $this->validatorsFile         = $validatorsFile;
        $this->realClassDirectory     = $realClassDirectory;
        $this->abstractClassDirectory = rtrim($abstractClassDirectory, '\\/');
        $this->outputDir              = $this->abstractClassDirectory . '/grace_new';
        $this->versionFilename        = $this->outputDir . '/version.txt';
        $this->extraPlugins           = $extraPlugins;
    }
    public function needUpdate()
    {
        $currentVersion   = md5(serialize($this->getConfig()));
        $generatedVersion = (file_exists($this->versionFilename) ? file_get_contents($this->versionFilename) : '');

        return ($currentVersion != $generatedVersion);
    }
    public function getConfig()
    {
        $loader = new ConfigLoader;
        $config = $loader->load($this->modelConfigResources);

        //если используется extends_config, то конфиг модели extends_config рекурсивно мержиться с текущим
        foreach ($config['models'] as $mName => $mConfig) {
            if (isset($mConfig['extends_config'])) {
                $parentConfigName = $mConfig['extends_config'];
                if (!isset($config['models'][$parentConfigName])) {
                    throw new \LogicException(
                        'Config ' . $parentConfigName . ' must be defined for using it as a parent');
                }
                $parentConfig             = $config['models'][$parentConfigName];
                $parentConfig['abstract'] = false;
                //TODO переопределение полей не работает потому что рекурсивный мерж хитрый как индюк
                $config['models'][$mName] = array_merge_recursive($parentConfig, $mConfig);
            }
        }

        //удаляем абстрактные конфиги
        foreach ($config['models'] as $mName => $mConfig) {
            if (isset($mConfig['abstract']) and $mConfig['abstract']) {
                unset($config['models'][$mName]);
            }
        }

        $config['extra-finders'] = $this->getExtraFindersConfig($this->namespace['common_prefix_without_slash'] . '\\' . $this->namespace['finder']);

        return $config;
    }
    public function generate()
    {
        $autoload = function($className)
        {
            $fileName = $this->outputDir . '/' . str_replace('\\', '/', ltrim($className, '\\')) . '.php';
            if (file_exists($fileName)) {
                require $fileName ;
            }
        };

        spl_autoload_register($autoload);

        $annotationReader = 'Grace\Bundle\CommonBundle\Annotations\FormData';

        $validatorsFile = $this->validatorsFile;

        $configFull = $this->getConfig();

        $config       = $configFull['models'];
        $nsConfig     = $this->namespace;
        $nsp          = $nsConfig['common_prefix_without_slash'] . '\\';
        $managerClass = $nsp . $nsConfig['manager_class'];
        $nsRecord     = $nsp . $nsConfig['record'];
        $nsFinder     = $nsp . $nsConfig['finder'];
        $nsCollection = $nsp . $nsConfig['collection'];
        $nsMapper     = $nsp . $nsConfig['mapper'];

        $this->cleanOutputDir();
        file_put_contents($this->versionFilename, md5(serialize($this->getConfig())));
        $this->initPlugins($this->defaultPlugins);
        $this->initPlugins($this->extraPlugins);

        foreach ($config as $modelName => &$modelConfig) {
            foreach ($this->plugins as $plugin) {
                $modelConfig = $plugin->prepareModelConfig($modelName, $modelConfig);
            }
        }

        $this->generateValidators($config, $nsRecord, $validatorsFile);
        $this->generateManager($config, $configFull['extra-finders'], $managerClass, $this->containerClass, $nsFinder);
        $this->generateRecords($config, $managerClass, $this->containerClass, $nsRecord, $annotationReader);
        $this->generateFinders($config, $managerClass, $this->containerClass, $nsFinder, $nsRecord, $nsCollection);
        $this->generateCollections($config, $nsCollection, $nsRecord);


        spl_autoload_unregister($autoload);

        shell_exec('rm -fR ' . $this->abstractClassDirectory . '/grace');
        shell_exec('mv ' . $this->abstractClassDirectory . '/grace_new ' . $this->abstractClassDirectory . '/grace');
        //shell_exec('cp -R ' . $this->abstractClassDirectory . '/grace_new ' . $this->abstractClassDirectory . '/grace');
    }
    private function initPlugins(array $pluginNames)
    {
        foreach ($pluginNames as $pluginName) {
            $plugin = new $pluginName;
            if (!($plugin instanceof PluginAbstract)) {
                throw new \Exception('Plugin ' . $pluginName . ' must be instance of PluginAbstract');
            }
            $this->plugins[] = $plugin;
        }
    }
    private function getExtraFindersConfig($finderNamespace)
    {
        $config = array();

        $namespaceDir = str_replace('\\', '/', $finderNamespace);
        $dir = $this->realClassDirectory . '/' . $namespaceDir . '/';

        $d = dir($dir);

        while (false !== ($filename = $d->read())) {

            if (is_dir($dir . $filename) and $filename != '..' and $filename != '.') {
                $baseFinder = $filename;
                $subdir = $dir . $filename . '/';

                $d2 = dir($subdir);

                while (false !== ($filename2 = $d2->read())) {

                    $baseFilename = pathinfo($filename2, PATHINFO_FILENAME);

                    if (is_dir($subdir . $filename2)) {
                        continue;
                    }

                    if (substr($baseFilename, -6) != 'Finder') {
                        continue;
                    } else {
                        $baseFilename = substr($baseFilename, 0, -6);
                    }

                    $config[$baseFinder . '\\' . $baseFilename] = $baseFinder;
                }

                $d2->close();
            }
        }

        $d->close();

        return $config;
    }
    private function generateValidators($config, $modelNamespace, $validatorsFile)
    {
        $validators = array();

        foreach ($config as $modelName => $modelContent) {
            $fieldsToValidate = array();

            foreach ($modelContent['properties'] as $propName => $propOptions) {
                if (!empty($propOptions['validator'])) {
                    $fieldsToValidate[$propName] = $propOptions['validator'];
                }
            }

            if (!empty($fieldsToValidate)) {
                $validators[$modelNamespace . '\\' . $modelName] = array('getters' => $fieldsToValidate);
            }
        }

        file_put_contents($validatorsFile, Yaml::dump($validators, 4));
    }
    private function generateManager($config, $extraFindersConfig, $managerClass, $containerClass, $finderNamespace)
    {
        $docblock = new PhpDoc;
        $docblock->setTags(array(
                                array(
                                    'name'        => 'method',
                                    'description' => '\\' . $containerClass . ' getContainer()'
                                ),
                           ));

        $realManager = new \Zend_CodeGenerator_Php_Class();
        $realManager
            ->setDocblock($docblock)
            ->setName(self::CONCRETE_CLASS_MANAGER)
            ->setExtendedClass(self::BASE_CLASS_MANAGER);

        //Connections and finders
        $connections = array();
        foreach ($config as $modelName => $modelConfig) {
            if (isset($modelConfig[self::CONFIG_CONNECTION]['name'])) {
                $connections[$modelName] = $modelConfig[self::CONFIG_CONNECTION]['name'];
            }
        }
        $defValue = new \Zend_CodeGenerator_Php_Property_DefaultValue();
        $defValue->setValue($connections);
        $property = new \Zend_CodeGenerator_Php_Property();
        $property
            ->setDefaultValue($defValue)
            ->setName('connectionNames')
            ->setVisibility(\Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED);
        $realManager->setProperty($property);

        //Instance finders
        foreach ($config as $modelName => $modelConfig) {
            $docblock = new PhpDoc();
            $docblock->setTag(array(
                                   'name'        => 'return',
                                   'description' => '\\' . $finderNamespace . '\\' . $modelName . 'Finder'
                              ));
            $managerMethod = new \Zend_CodeGenerator_Php_Method;
            $managerMethod
                ->setDocblock($docblock)
                ->setName('get' . $modelName . 'Finder')
                ->setBody('return $this->getFinder(\'' . $modelName . '\');');
            $realManager->setMethod($managerMethod);
        }

        //Extra finders
        foreach ($extraFindersConfig as $extraFinderName => $extraFinderClassName) {
            $docblock = new PhpDoc();
            $docblock->setTag(array(
                                   'name'        => 'return',
                                   'description' => '\\' . $finderNamespace . '\\' . $extraFinderName . 'Finder'
                              ));
            $managerMethod = new \Zend_CodeGenerator_Php_Method;
            $managerMethod
                ->setDocblock($docblock)
                ->setName('get' . str_replace('\\', '', $extraFinderName) . 'Finder')
                ->setBody("return \$this->getFinder('$extraFinderClassName', '$extraFinderName');");
            $realManager->setMethod($managerMethod);
        }

        $managerClass = str_replace('\\', '/', $managerClass);
        //Some\AppBundle\ORMManager => ORMManager
        $filename = pathinfo($managerClass, PATHINFO_BASENAME);
        //Some\AppBundle\ORMManager => ORMManager
        $namespace = pathinfo($managerClass, PATHINFO_DIRNAME);
        $namespace = str_replace('/', '\\', $namespace);

        $this->writeFile($filename . '.php', $namespace, $realManager);
    }
    private function generateRecords($config, $managerClass, $containerClass, $namespace, $annotationReader)
    {
        foreach ($config as $modelName => $modelConfig) {
            $docblock = new PhpDoc;
            $docblock->setTags(array(
                array(
                    'name' => 'method',
                    'description' => '\\' . $namespace . '\\' . $modelName . ' getOriginalRecord()'
                ),
                array(
                    'name' => 'method',
                    'description' => '\\' . $managerClass . ' getOrm()'
                ),
                array(
                    'name' => 'method',
                    'description' => '\\' . $containerClass . ' getContainer()'
                ),
            ));

            $parent = isset($modelConfig['extends']) ? $modelConfig['extends'] : self::BASE_CLASS_RECORD;

            $recordAbstract = new \Zend_CodeGenerator_Php_Class();
            $recordAbstract
                ->setName($modelName . 'Abstract')
                ->setAbstract(true)
                ->setExtendedClass($parent)
                ->setDocblock($docblock);

            $recordConcrete = new \Zend_CodeGenerator_Php_Class();
            $recordConcrete
                ->setName($modelName)
                ->setExtendedClass($modelName . 'Abstract');


            foreach ($this->plugins as $plugin) {
                $recordAbstract->setProperties($plugin->getAbstractRecordProperties($modelName, $modelConfig, $namespace, $parent));
                $recordAbstract->setMethods($plugin->getAbstractRecordMethods($modelName, $modelConfig, $namespace, $parent));
            }


            $fields = $modelConfig[self::CONFIG_PROPERTIES];


            $fieldsPropertyArray     = array();
            $noDbFieldsPropertyArray = array();
            foreach ($fields as $fieldName => $fieldConfig) {
                //$fieldsPropertyArray[] = "\n\t'" . $fieldName . "',";
                if (empty($fieldConfig['mapping'])) {
                    $noDbFieldsPropertyArray[] = $fieldName;
                } else {
                    $fieldsPropertyArray[] = $fieldName;
                }
            }


            $fieldsProperty = (new \Zend_CodeGenerator_Php_Property)
                ->setName(self::MAPPER_FIELDS_ARRAY)
                ->setVisibility(\Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED)
                ->setStatic(true)
                ->setDefaultValue((new \Zend_CodeGenerator_Php_Property_DefaultValue)->setValue($fieldsPropertyArray));

            $noDbFieldsProperty = (new \Zend_CodeGenerator_Php_Property)
                ->setName(self::MAPPER_NO_DB_FIELDS_ARRAY)
                ->setVisibility(\Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED)
                ->setStatic(true)
                ->setDefaultValue((new \Zend_CodeGenerator_Php_Property_DefaultValue)->setValue($noDbFieldsPropertyArray));


            $recordAbstract->setProperty($fieldsProperty);
            $recordAbstract->setProperty($noDbFieldsProperty);



            foreach ($fields as $fieldName => $fieldConfig) {
                if ($fieldName != 'id') {

                    if (!static::nonAbstractMethodExists($parent, 'get' . ucfirst($fieldName)) and $recordAbstract->getMethod('get' . ucfirst($fieldName)) === false) {

                        $typeStr = isset($fieldConfig['type']) ? '(' . $fieldConfig['type'] . ')' : '';
                        $body    = 'return ' . $typeStr . ' $this->fields[\'' . $fieldName . '\'];';

                        $method = new \Zend_CodeGenerator_Php_Method;
                        $method
                            ->setName('get' . ucfirst($fieldName))
                            ->setBody($body);

                        $recordAbstract->setMethod($method);
                    }

                    if (!static::nonAbstractMethodExists($parent, 'set' . ucfirst($fieldName)) and $recordAbstract->getMethod('set' . ucfirst($fieldName)) === false) {
                        $parameter = new \Zend_CodeGenerator_Php_Parameter;
                        $parameter->setName($fieldName);
                        $method = new \Zend_CodeGenerator_Php_Method;

                        $typeStr = isset($fieldConfig['type']) ? '(' . $fieldConfig['type'] . ')' : '';
                        $body    =
                            '$this->fields[\'' . $fieldName . '\'] = ' . $typeStr . ' $' . $fieldName .
                                ';' . "\n" . '$this->markAsChanged();' . "\n" . 'return $this;';

                        $method
                            ->setName('set' . ucfirst($fieldName))
                            ->setParameter($parameter)
                            ->setBody($body);
                        $recordAbstract->setMethod($method);
                    }

                    foreach ($this->plugins as $plugin) {
                        $getterObject = $recordAbstract->getMethod('get' . ucfirst($fieldName));
                        $getterObject = $plugin->updateGetterDoctype($fieldName, $fieldConfig, $getterObject);
                    }
                }
            }

            $this->writeFile($modelName . 'Abstract.php', $namespace, $recordAbstract, $annotationReader);
            $this->writeFile($modelName . '.php', $namespace, $recordConcrete, $annotationReader);
        }
    }

    private static function nonAbstractMethodExists($class, $method)
    {
        if (!method_exists($class, $method)) {
            return false;
        }

        $rc = new \ReflectionClass($class);
        $rm = $rc->getMethod($method);
        if ($rm->isAbstract()) {
            return false;
        }

        return true;
    }

    private function generateFinders($config, $managerClass, $containerClass, $namespace, $recordNamespace, $collectionNamespace)
    {
        foreach ($config as $modelName => $modelConfig) {
            $docblock = new PhpDoc;
            $docblock->setTags(array(
                                    array(
                                        'name'        => 'method',
                                        'description' => '\\' . $managerClass . ' getOrm()'
                                    ),
                                    array(
                                        'name'        => 'method',
                                        'description' => '\\' . $containerClass . ' getContainer()'
                                    ),
                                    array(
                                        'name'        => 'method',
                                        'description' => '\\' . $recordNamespace . '\\' . $modelName . ' getById($id)'
                                    ),
                                    array(
                                        'name'        => 'method',
                                        'description' => '\\' . $recordNamespace . '\\' . $modelName . '|bool getByIdOrFalse($id)'
                                    ),
                                    array(
                                        'name'        => 'method',
                                        'description' => '\\' . $recordNamespace . '\\' . $modelName . ' create(array $newParams = array(), $idWhenNecessary = null)'
                                    ),
                                    array(
                                        'name'        => 'method',
                                        'description' => '\\' . $recordNamespace . '\\' . $modelName . ' fetchOne()'
                                    ),
                                    array(
                                        'name'        => 'method',
                                        'description' => '\\' . $recordNamespace . '\\' . $modelName . '|bool fetchOneOrFalse()'
                                    ),
                                    array(
                                        'name'        => 'method',
                                        'description' =>
                                        '\\' . $collectionNamespace . '\\' . $modelName . 'Collection fetchAll()'
                                    ),
                               ));
            $finderAbstract = new \Zend_CodeGenerator_Php_Class();
            $finderAbstract
                ->setName($modelName . 'FinderAbstract')
                ->setAbstract(true)
                ->setDocblock($docblock);



            if (!isset($modelConfig[self::CONFIG_CONNECTION]['type'])) {
                $modelConfig[self::CONFIG_CONNECTION]['type'] = '';
            }

            switch ($modelConfig[self::CONFIG_CONNECTION]['type']) {
                case 'crud':
                    $finderAbstract->setExtendedClass(self::BASE_CLASS_FINDER_CRUD);
                    break;
                case'sql':
                default:
                    $finderAbstract->setExtendedClass(self::BASE_CLASS_FINDER_SQL);
            }



            $finderConcrete = new \Zend_CodeGenerator_Php_Class();
            $finderConcrete
                ->setName($modelName . 'Finder')
                ->setExtendedClass($modelName . 'FinderAbstract');

            $this->writeFile($modelName . 'FinderAbstract.php', $namespace, $finderAbstract);
            $this->writeFile($modelName . 'Finder.php', $namespace, $finderConcrete);
        }
    }
    private function generateCollections($config, $namespace, $recordNamespace)
    {
        foreach ($config as $modelName => $modelConfig) {
            $fields = $modelConfig[self::CONFIG_PROPERTIES];
            $parent = isset($modelConfig['extends']) ? $modelConfig['extends'] : self::BASE_CLASS_RECORD;

            $collectionAbstractMethods = array();
            foreach ($fields as $fieldName => $fieldConfig) {
                $parameter = new \Zend_CodeGenerator_Php_Parameter;
                $parameter->setName($fieldName);
                $method = new \Zend_CodeGenerator_Php_Method;
                $method
                    ->setName('set' . ucfirst($fieldName))
                    ->setParameter($parameter)
                    ->setBody('foreach ($this as $record) {' . "\n    " . '$record->set' . ucfirst($fieldName) . '($' .
                                  $fieldName . ');' . "\n" . '}' . "\n" . 'return $this;');
                $collectionAbstractMethods[] = $method;
            }


            foreach ($this->getModelMethods($recordNamespace, $modelName) as $refMethod) {
                /* @var \Zend_Reflection_Method $refMethod */
                $params = array();
                foreach ($refMethod->getParameters() as $refParam) {
                    /* @var \Zend_Reflection_Parameter $refParam */
                    $params[] = '$' . $refParam->getName();
                }
                $paramString = implode(', ', $params);
                $method      = \Zend_CodeGenerator_Php_Method::fromReflection($refMethod);
                foreach ($method->getParameters() as $param) {
                    /** @var $param \Zend_CodeGenerator_Php_Parameter */
                    $paramType = $param->getType();
                    if (strpos($paramType, '\\') !== false) {
                        $paramType = '\\' . $paramType;
                    }
                    $param->setType($paramType);
                }

                $method->setBody(
                    "" . 'foreach ($this as $record) {' . "\n    " . '$record->' . $refMethod->getName() . '(' .
                        $paramString . ');' . "\n" . '}' . "\n" . 'return $this;');

                $collectionAbstractMethods[] = $method;
            }

            $collectionAbstract = new \Zend_CodeGenerator_Php_Class();
            $collectionAbstract
                ->setName($modelName . 'CollectionAbstract')
                ->setAbstract(true)
                ->setExtendedClass(isset($modelConfig['collection_extends']) ? $modelConfig['collection_extends'] : self::BASE_CLASS_COLLECTION)
                ->setMethods($collectionAbstractMethods);

            $collectionConcrete = new \Zend_CodeGenerator_Php_Class();
            $collectionConcrete
                ->setName($modelName . 'Collection')
                ->setExtendedClass($modelName . 'CollectionAbstract');


            foreach ($this->plugins as $plugin) {
                $collectionAbstract->setProperties($plugin->getAbstractCollectionProperties($modelName, $modelConfig,
                                                                                        $namespace));
                $collectionAbstract->setMethods($plugin->getAbstractCollectionMethods($modelName, $modelConfig,
                                                                                  $namespace));
            }

            $this->writeFile($modelName . 'CollectionAbstract.php', $namespace, $collectionAbstract);
            $this->writeFile($modelName . 'Collection.php', $namespace, $collectionConcrete);
        }
    }
    private function getModelMethods($namespace, $modelName)
    {
        $modelClass = $namespace . '\\' . $modelName;
        if (class_exists($modelClass)) {
            //сначала нужно отфильтровать методы родителей и оставить только методы конкретного класса
            $abstractRefClass = new \Zend_Reflection_Class($modelClass . 'Abstract');
            $abstractMethods  = $abstractRefClass->getMethods(\ReflectionMethod::IS_PUBLIC);

            $refClass   = new \Zend_Reflection_Class($modelClass);
            $allMethods = $refClass->getMethods(\ReflectionMethod::IS_PUBLIC);

            //исключение статических методов через фильтр не работает (\ReflectionMethod::IS_PUBLIC & ~\ReflectionMethod::IS_STATIC
            //поэтому ниже проверка руками

            $abstractMethodsNames = array();
            foreach ($abstractMethods as $abstractMethod) {
                $abstractMethodsNames[] = $abstractMethod->name;
            }

            $methods = array();
            foreach ($allMethods as $method) {
                /* @var \Zend_Reflection_Method $method */
                if (!in_array($method->name, $abstractMethodsNames) and !$this->isGetter($method->name) and
                    !$method->isAbstract() and !$method->isStatic()
                ) {
                    $methods[] = $method;
                }
            }

            return $methods;
        }
        return array();
    }
    private function isGetter($methodName)
    {
        $getterPrefixes = explode(',', 'get,fetch,is,has');
        foreach ($getterPrefixes as $prefix) {
            if (strpos($methodName, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }
    private function cleanOutputDir()
    {
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir);
        }
        shell_exec('rm -fR ' . $this->outputDir . '/*');
    }
    private function writeFile($filename, $namespace, \Zend_CodeGenerator_Php_Class $class, $use = '')
    {
        $namespaceDir = str_replace('\\', '/', $namespace);
        $realFilename = $this->realClassDirectory . '/' . $namespaceDir . '/' . $filename;

        if (!file_exists($realFilename)) {
            $file = new \Zend_CodeGenerator_Php_File;
            $body = '';
            if ($namespace != '') {
                $body .= "/* Zend_CodeGenerator_Php_File-NamespaceMarker */\nnamespace " . $namespace . ';' . "\n";
            }
            if ($use != '') {
                $body .= "/* Zend_CodeGenerator_Php_File-UseMarker */\nuse " . $use . ';' . "\n";
            }
            $file->setBody($body);
            $file->setClass($class);

            $dir = $this->outputDir . '/' . $namespaceDir;
            if (!is_dir($dir)) {
                shell_exec('mkdir -p ' . $dir);
            }
            $genFilename = $dir . '/' . $filename;

            $file->setFilename($genFilename);
            $file->write();
        }
    }
}

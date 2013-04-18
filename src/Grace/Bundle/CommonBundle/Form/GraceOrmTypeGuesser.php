<?php
namespace Grace\Bundle\CommonBundle\Form;

use Symfony\Component\Form\FormTypeGuesserInterface,
    Symfony\Component\Form\Guess\Guess,
    Symfony\Component\Form\Guess\TypeGuess,
    Symfony\Component\Form\Guess\ValueGuess,
    Doctrine\Common\Annotations\Reader,
    Grace\ORM\RecordAbstract,
    Grace\ORM\ORMManagerAbstract,
    Grace\Bundle\CommonBundle\Annotations\FormData;

class GraceOrmTypeGuesser implements FormTypeGuesserInterface
{
    const DEFAULT_LIST_GETTER = 'getList';
    const DEFAULT_FIELD_TYPE  = 'text';

    /** @var Reader */
    protected $reader;
    /** @var ORMManagerAbstract */
    protected $orm;

    private $typeTranslations = array(
        'select'               => 'choice',
    );

    private $cache;

    public function __construct(Reader $reader, ORMManagerAbstract $orm, array $extraTypeTranslations = array())
    {
        $this->reader = $reader;
        $this->orm    = $orm;
        $this->typeTranslations = array_merge($this->typeTranslations, $extraTypeTranslations);
    }

    /**
     * {@inheritDoc}
     */
    public function guessType($class, $property)
    {
        $fieldNameLowerCase = strtolower($property);
        $type               = static::DEFAULT_FIELD_TYPE;

        if (!$metadata = $this->getMetadata($class, $property)) {
            return new TypeGuess($type, array(), Guess::LOW_CONFIDENCE);
        }

        //копируем из аннотации, но убираем все, что не относится к форме
        $options     = $metadata;
        $keysToUnset = array('type', '_dataSource');
        foreach ($keysToUnset as $keyToUnset) {
            if (isset($options[$keyToUnset])) {
                unset($options[$keyToUnset]);
            }
        }

        if (!empty($metadata['type'])) {
            if (array_key_exists($metadata['type'], $this->typeTranslations)) {
                $translated = $this->typeTranslations[$metadata['type']];
                if ($translated[0] == '\\' && class_exists($translated)) {
                    $translated = new $translated();
                }
                $type = $translated;
            } else {
                $type = $metadata['type'];
            }
            $confidence = Guess::VERY_HIGH_CONFIDENCE;
        } else {
            if (preg_match('e([-\s]*)mail', $fieldNameLowerCase)) {
                $type = 'email';
            } else {
                if (strpos($fieldNameLowerCase, 'url') !== false) {
                    $type = 'url';
                }
            }
            $confidence = Guess::MEDIUM_CONFIDENCE;
        }

        //$type == 'choice' and  убрано, т.к. например тип color тоже использует _dataSource
        if (!empty($metadata['_dataSource'])) {
            $dataSource   = explode('\\', $metadata['_dataSource']);
            $finderClass  = array_shift($dataSource);
            $listGetter   = array_shift($dataSource) ? : static::DEFAULT_LIST_GETTER;
            $finderGetter = "get{$finderClass}";

            if (method_exists($this->orm, $finderGetter)) {
                $finder = $this->orm->$finderGetter();
                if (is_object($finder) && method_exists($finder, $listGetter)) {
                    $options['choices'] = $finder->$listGetter();
                }
            }
        }

        return new TypeGuess($type, $options, $confidence);
    }

    /**
     * {@inheritDoc}
     */
    public function guessRequired($class, $property)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function guessMaxLength($class, $property)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function guessMinLength($class, $property)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function guessPattern($class, $property)
    {
    }

    protected function getMetadata($class, $property)
    {
        if (isset($this->cache[$class][$property])) {
            return $this->cache[$class][$property];
        }

        $this->cache[$class][$property] = array();

        $getterName = 'get' . ucfirst($property);
        $modelClass = new \ReflectionClass($class);

        if ($modelClass->hasMethod($getterName)) {
            $getterMethod = $modelClass->getMethod($getterName);

            foreach ($this->reader->getMethodAnnotations($getterMethod) as $annotation) {
                if ($annotation instanceof FormData) {
                    /** @var $annotation FormData */
                    $this->cache[$class][$property] = $annotation->getData();
                    break;
                }
            }
        }

        return $this->cache[$class][$property];
    }
}

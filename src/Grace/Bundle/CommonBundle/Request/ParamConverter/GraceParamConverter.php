<?php

namespace Grace\Bundle\CommonBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\DoctrineBundle\Registry;

use Grace\ORM\ManagerAbstract;
use Grace\ORM\ExceptionNoResult;

/**
 * Grace param converter
 * Examples:
 * Company
 * Order
 * newCompany
 * collectionCompany:ActivatedCompanies
 */
class GraceParamConverter implements ParamConverterInterface
{
    protected $orm;

    public function __construct(ManagerAbstract $orm)
    {
        $this->orm = $orm;
    }

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $classes = explode(',', $configuration->getClass());

        foreach ($classes as $class) {
            $class = trim($class);

            if (strpos($class, 'finder') === 0) {
                $class = substr($class, 6);
                $type  = 'finder';
            } elseif (strpos($class, 'new') === 0) {
                $class = substr($class, 3);
                $type  = 'new';
            } else {
                $type = 'byId';
            }

            $getFinderMethod = 'get' . $class . 'Finder';
            if (method_exists($this->orm, $getFinderMethod)) {
                if ($type == 'finder') {
                    $model = $this->orm->$getFinderMethod();
                } elseif ($type == 'new') {
                    $model = $this->orm->$getFinderMethod()->create();
                } else {
                    if ($request->attributes->has('id')) {
                        try {
                            $model = $this->orm->$getFinderMethod()->getById($request->attributes->get('id'));
                        } catch (ExceptionNoResult $e) {
                            $excToThrow = $e;
                        }
                    }
                }
            }

            if (isset($model)) {
                $request->attributes->set($configuration->getName(), $model);

                return;
            }
        }

        if (isset($excToThrow)) {
            throw $excToThrow;
        }
    }

    public function supports(ConfigurationInterface $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return true;
    }
}

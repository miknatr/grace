<?php

namespace Grace\Bundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;

use Grace\ORM\Grace;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Grace param converter
 * Examples:
 *
 * @ParamConverter("company", class="Company")
 * public function someAction(Company $company) { ... }
 *
 * @ParamConverter("company", class="newCompany")
 * public function someAction(Company $company) { ... }

 * @ParamConverter("finder", class="finderCompany")
 * public function someAction(CompanyFinder $finder) { ... }
 */
class GraceParamConverter implements ParamConverterInterface
{
    protected $orm;

    public function __construct(Grace $orm)
    {
        $this->orm = $orm;
    }

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        /** @var $configuration ParamConverter */
        $rawClass = $configuration->getClass();
        $type     = $this->filterType($rawClass);
        $class    = $this->filterClass($rawClass);

        switch ($type) {
            case 'finder':
                $newAttr = $this->orm->getFinder($class);
                break;
            case 'new':
                $newAttr = $this->orm->getFinder($class)->create();
                break;
            case 'id':
            default:
                if (!$request->attributes->has('id')) {
                    throw new NotFoundHttpException();
                }

                $newAttr = $this->orm->getFinder($class)->getByIdOrFalse($request->attributes->get('id'));

                if (!$newAttr) {
                    throw new NotFoundHttpException();
                }

                break;
        }

        $request->attributes->set($configuration->getName(), $newAttr);
    }

    public function supports(ConfigurationInterface $configuration)
    {
        /** @var $configuration ParamConverter */
        if (null === $configuration->getClass()) {
            return false;
        }

        // Is it real class name? Names like Company, newCompany and finderCompany are accepted
        if (strpos($configuration->getClass(), '\\') !== false) {
            return false;
        }

        // Is there suitable finder in orm?
        return (null === $this->orm->getFinder($this->filterClass($configuration->getClass())));
    }

    private function filterClass($class)
    {
        if (strpos($class, 'finder') === 0) {
            return substr($class, 6);
        } elseif (strpos($class, 'new') === 0) {
            return substr($class, 3);
        }

        return $class;
    }

    private function filterType($class)
    {
        if (strpos($class, 'finder') === 0) {
            return 'finder';
        } elseif (strpos($class, 'new') === 0) {
            return 'new';
        }

        return 'id';
    }
}

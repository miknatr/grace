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
        $classes = explode(',', $configuration->getClass());

        foreach ($classes as $class) {
            $class = trim($class);

            if (strpos($class, 'finder') === 0) {
                $class = substr($class, 6);
                $model = $this->orm->getFinder($class);
            } elseif (strpos($class, 'new') === 0) {
                $class = substr($class, 3);
                $model = $this->orm->getFinder($class)->create();
            } else {
                if ($request->attributes->has('id')) {
                    $model = $this->orm->getFinder($class)->getByIdOrFalse($request->attributes->get('id'));
                } else {
                    throw new NotFoundHttpException();
                }
            }

            $request->attributes->set($configuration->getName(), $model);
        }
    }

    public function supports(ConfigurationInterface $configuration)
    {
        /** @var $configuration ParamConverter */
        if (null === $configuration->getClass()) {
            return false;
        }

        return true;
    }
}

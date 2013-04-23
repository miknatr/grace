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

class ClassNameProvider
{
    protected $modelNamespace;
    protected $finderNamespace;

    const FINDER_NAMESPACE_PART = 'Finder';
    const FINDER_SUFFIX = 'Finder';
    const MODEL_NAMESPACE_PART = 'Model';

    public function __construct($commonNamespace)
    {
        $commonNamespace = trim($commonNamespace, '\\');

        if ($commonNamespace == '') {
            throw new \LogicException('Common namespace must be provided');
        }

        $this->finderNamespace = '\\' . $commonNamespace . '\\' . self::FINDER_NAMESPACE_PART;
        $this->modelNamespace  = '\\' . $commonNamespace . '\\' . self::MODEL_NAMESPACE_PART;
    }
    public function getBaseClass($baseOrModelOrFinderClass)
    {
        if (strpos($baseOrModelOrFinderClass, '\\') === false) {
            return $baseOrModelOrFinderClass;
        }


        $baseOrModelOrFinderClass = '\\' . trim($baseOrModelOrFinderClass, '\\');

        if (strpos($baseOrModelOrFinderClass, $this->modelNamespace) === 0) {
            return substr($baseOrModelOrFinderClass, strlen($this->modelNamespace) + 1);
        }

        if (strpos($baseOrModelOrFinderClass, $this->finderNamespace) === 0 and substr($baseOrModelOrFinderClass, -strlen(self::FINDER_SUFFIX)) == self::FINDER_SUFFIX) {
            return substr(substr($baseOrModelOrFinderClass, 0, -strlen(self::FINDER_SUFFIX)), strlen($this->finderNamespace) + 1);
        }

        throw new \LogicException('Unsupported class name ' . $baseOrModelOrFinderClass);
    }
    public function getModelClass($baseOrModelOrFinderClass)
    {
        return $this->modelNamespace . '\\' . $this->getBaseClass($baseOrModelOrFinderClass);
    }
    public function getFinderClass($baseOrModelOrFinderClass)
    {
        return $this->finderNamespace . '\\' . $this->getBaseClass($baseOrModelOrFinderClass) . self::FINDER_SUFFIX;
    }
}

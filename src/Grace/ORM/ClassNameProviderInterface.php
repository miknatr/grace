<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM;

/**
 * Gets full class names by base class name
 */
interface ClassNameProviderInterface
{
    /**
     * Gets base class name by full model (record) class name
     * @abstract
     * @param string $modelClass
     * @return string base class name
     */
    public function getBaseClass($modelClass);
    /**
     * Gets base class name by full finder class name
     * @abstract
     * @param string $finderClass
     * @return string base class name
     */
    public function getBaseClassFromFinderClass($finderClass);
    /**
     * Gets full model class name by base class name
     * @abstract
     * @param string $baseClass
     * @return string model class name
     */
    public function getModelClass($baseClass);
    /**
     * Gets full finder class name by base class name
     * @abstract
     * @param string $baseClass
     * @return string model class name
     */
    public function getFinderClass($baseClass);
    /**
     * Gets full mapper class name by base class name
     * @abstract
     * @param string $baseClass
     * @return string model class name
     */
    public function getMapperClass($baseClass);
}

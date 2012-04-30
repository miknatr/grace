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

interface ClassNameProviderInterface
{
    public function getBaseClass($modelClass);
    public function getModelClass($baseClass);
    public function getFinderClass($baseClass);
    public function getMapperClass($baseClass);
    public function getCollectionClass($baseClass);
}
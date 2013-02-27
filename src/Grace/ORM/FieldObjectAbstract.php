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

use Grace\SQLBuilder\SqlValueInterface;

/**
 * Abstract field object
 */
abstract class FieldObjectAbstract implements SqlValueInterface
{
    /**
     * @return string|SqlValue
     */
    public function getSqlValue()
    {
        throw new \LogicException('You must implement getSqlValue or getSql+getValues methods');
    }

    /**
     * @return string
     */
    public function getSql()
    {
        return '?q';
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return array($this->getSqlValue());
    }
}
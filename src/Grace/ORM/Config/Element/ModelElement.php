<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM\Config\Element;

use Grace\ORM\Config\Element\ParentElement;
use Grace\ORM\Config\Element\PropertyElement;

class ModelElement
{
    /** @var PropertyElement[] */
    public $properties;
    /** @var ParentElement[] */
    public $parents;
}

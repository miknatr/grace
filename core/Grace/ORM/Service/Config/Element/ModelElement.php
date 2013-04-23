<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM\Service\Config\Element;

use Grace\ORM\Service\Config\Element\ParentElement;
use Grace\ORM\Service\Config\Element\PropertyElement;

class ModelElement
{
    /** @var PropertyElement[] propertyNames as keys*/
    public $properties = array();
    /** @var ParentElement[] propertyNames linked to parent id as keys*/
    public $parents = array();
}

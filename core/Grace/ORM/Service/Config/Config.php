<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM\Service\Config;

use Grace\ORM\Service\Config\Element\ModelElement;

class Config
{
    /** @var ModelElement[] modelNames as keys*/
    public $models = array();
}

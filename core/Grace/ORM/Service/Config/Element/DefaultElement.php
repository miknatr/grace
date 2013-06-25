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

use Grace\ORM\Type\TypeTimestamp;

class DefaultElement
{
    private $definition;

    public function __construct($default)
    {
        $this->definition = $default;
    }

    public function getValue()
    {
        return $this->definition == 'now' ? TypeTimestamp::format(time()) : $this->definition;
    }
}

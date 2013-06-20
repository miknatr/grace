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

class PropertyElement
{
    /** @var MappingElement */
    public $mapping;

    /**
     * Grace haven't got own validation service
     * So, validation from config is loaded "as is" and custom validation service has to handle it.
     * @var mixed
     */
    public $validation;
}

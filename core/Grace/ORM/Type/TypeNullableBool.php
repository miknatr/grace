<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM\Type;

class TypeNullableBool extends TypeBool
{
    public function getAlias()
    {
        return 'nullable_bool';
    }

    public function isNullable()
    {
        return true;
    }
}

<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\TypeConverter;

interface TypeInterface
{
    public function getTypeName();
    public function getPhpTypeName();
    public function getDbTypeName();
    public function convertDbToPhp($value);
    public function convertOnSetter($value);
    public function convertPhpToDb($value);
}

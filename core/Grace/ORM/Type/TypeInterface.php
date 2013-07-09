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

interface TypeInterface
{
    public function getAlias();
    public function getPhpType();
    public function getSetterPhpdocType();
    public function getDbType();

    /**
     * Generates a code expression for db-to-php data/type conversion
     *
     * The code expression should assume the value from DB is stored in $value.
     *
     * Remember to use fully qualified class names (with namespaces)
     * if you use any classes in the generated code!
     *
     * @return string
     */
    public function getDbToPhpConverterCode();

    /**
     * Generates a code expression for an empty value
     *
     * Remember to use fully qualified class names (with namespaces)
     * if you use any classes in the generated code!
     *
     * @return string
     */
    public function getPhpDefaultValueCode();

    public function convertOnSetter($value);
    public function convertPhpToDb($value);
    public function isNullable();
}

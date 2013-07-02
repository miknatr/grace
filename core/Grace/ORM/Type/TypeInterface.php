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
    public function getDbType();

    /**
     * Generates a code block for db-to-php data/type conversion
     *
     * The code block should assume the value from DB is stored in $value.
     *
     * $returnIntoExpression will have a code string like '$a =',
     * which should be inserted into the code generated by type converter
     * as a receiver for the converted value.
     *
     * Remember to use fully qualified class names (with namespaces)
     * if you use any classes in the generated code!
     *
     * @param string $returnIntoExpression
     * @return string
     */
    public function getDbToPhpConverterCode($returnIntoExpression);
    public function convertOnSetter($value);
    public function convertPhpToDb($value);
    public function getPhpDefaultValue();
}

<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\DBAL\Exception;

/**
 * Connection error
 */
class ConnectionException extends \Exception
{
    const E_CONNECTION       = 0; // default
    const E_NO_DRIVER_IN_PHP = 1;
}

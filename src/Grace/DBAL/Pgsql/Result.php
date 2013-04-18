<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Alex Polev <alex.v.polev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\DBAL\Pgsql;

use Grace\DBAL\AbstractConnection\ResultAbstract;
use Grace\DBAL\Exception\ConnectionException;

/**
 * Pgsql result concrete class
 */
class PgsqlResult extends ResultAbstract
{
    /** @var resource */
    private $result;

    /**
     * @inheritdoc
     */
    public function fetchOneOrFalse()
    {
        return pg_fetch_assoc($this->result);
    }

    /**
     * @param $result
     */
    public function __construct($result)
    {
        if (!is_resource($result)) {
            throw new ConnectionException('Result should be a valid resource.');
        }

        $this->result = $result;
    }
    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        pg_free_result($this->result);
    }
}

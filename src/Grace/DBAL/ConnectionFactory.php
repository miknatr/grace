<?php

namespace Grace\DBAL;

use Grace\DBAL\AbstractConnection\InterfaceConnection;
use Grace\DBAL\Mysqli\Connection;
use Grace\DBAL\Pgsql\PgsqlConnection;

class ConnectionFactory
{
    /**
     * @param array $config
     * @return InterfaceConnection
     * @throws \LogicException
     */
    static public function getConnection(array $config)
    {
        if (empty($config['adapter'])) {
            throw new \LogicException('Adapter type must be defined');
        }

        switch ($config['adapter']) {
            case 'mysqli':
                return new Connection($config['host'], $config['port'], $config['user'], $config['password'], $config['database']);
            case 'pgsql':
                return new PgsqlConnection($config['host'], $config['port'], $config['user'], $config['password'], $config['database']);
        }

        throw new \LogicException('Unsupported adapter type ' . $config['adapter']);
    }
}

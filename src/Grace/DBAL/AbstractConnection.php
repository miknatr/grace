<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\DBAL;

use Grace\SQLBuilder\Factory;

/**
 * Provides some base functions for concrete connection classes
 */
abstract class AbstractConnection implements InterfaceConnection
{

    /**
     * @var QueryLogger
     */
    private $logger;
    /**
     * @inheritdoc
     */
    public function setLogger(QueryLogger $logger)
    {
        $this->logger = $logger;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function getLogger()
    {
        if (!is_object($this->logger)) {
            $this->setLogger(new QueryLogger());
        }
        return $this->logger;
    }
    /**
     * @inheritdoc
     */
    public function getSQLBuilder()
    {
        return new Factory($this);
    }
    /**
     * @inheritdoc
     */
    public function replacePlaceholders($query, array $arguments)
    {
        $position = 0;
        foreach ($arguments as $value) {
            $position = strpos($query, '?', $position);
            if ($position !== false) {
                $bindType    = $query[$position + 1];
                $replacement = $this->escapeValueByType($value, $bindType);
                $query       = substr_replace($query, $replacement, $position, 2);
                $position    = $position + strlen($value);
            }
        }
        return $query;
    }
    /**
     * Escapes value in compliance with type
     * @param mixed $value
     * @param char  $type
     * @return string
     */
    private function escapeValueByType($value, $type)
    {
        if (is_object($value)) {
            $value = (string) $value;
        }

        switch ($type) {
            case 'p':
                $r = $value;
                break;
            case 'e':
                $r = $this->escape($value);
                break;
            case 'q':
            default:
                $r = "'" . $this->escape($value) . "'";
        }
        return $r;
    }
}
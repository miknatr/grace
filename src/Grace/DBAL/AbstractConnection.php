<?php

namespace Grace\DBAL;

use Grace\EventDispatcher\Dispatcher;
use Grace\SQLBuilder\Factory;

abstract class AbstractConnection implements InterfaceConnection
{
    private $config = array();
    private $dispatcher;

    public function __construct(array $config, Dispatcher $dispatcher)
    {
        $this->config     = $config;
        $this->dispatcher = $dispatcher;
    }
    public function getSQLBuilder()
    {
        return new Factory($this);
    }
    /**
     * @return Dispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->dispatcher;
    }
    public function replacePlaceholders($query, array $arguments)
    {
        $position = 0;
        foreach ($arguments as $value) {
            $position = strpos($query, '?', $position);
            if ($position !== false) {
                $bindType    = $query[$position + 1];
                $replacement = $this->getEscapedValueByType($value, $bindType);
                $query       = substr_replace($query, $replacement, $position, 2);
                $position    = $position + strlen($value);
            }
        }
        return $query;
    }
    private function getEscapedValueByType($value, $type)
    {
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
    protected function getConfig()
    {
        return $this->config;
    }
}
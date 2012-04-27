<?php

namespace Grace\DBAL;

abstract class AbstractResult implements InterfaceResult
{
    public function fetchAll()
    {
        $r = array();
        while ($row = $this->fetchOne()) {
            $r[] = $row;
        }
        return $r;
    }
    public function fetchColumn()
    {
        $r = array();
        while ($row = $this->fetchOne()) {
            $result = array_shift($row);
            $r[]    = $result;
        }
        return $r;
    }
    public function fetchResult()
    {
        $row = $this->fetchOne();
        return array_shift($row);
    }
}
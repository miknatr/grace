<?php

namespace Grace\ORM;

interface ManagerInterface {
    public function getFinder($className);
    public function getMapper($className);
    public function commit($className);
}
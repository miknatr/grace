<?php

namespace Grace\SQLBuilder;

use Grace\DBAL\InterfaceExecutable;

class InsertBuilder extends AbstractBuilder {
    protected function getQueryString() {
        return '';
    }
    protected function getQueryArguments() {
        return array();
    }
}
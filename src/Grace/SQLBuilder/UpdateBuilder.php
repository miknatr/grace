<?php

namespace Grace\SQLBuilder;

use Grace\DBAL\InterfaceExecutable;

class UpdateBuilder extends AbstractBuilder {
    protected function getQueryString() {
        return '';
    }
    protected function getQueryArguments() {
        return array();
    }
}
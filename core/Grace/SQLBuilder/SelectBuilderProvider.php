<?php

use Grace\SQLBuilder\SelectBuilder;

interface SelectBuilderProvider
{
    /**
     * @param string $table
     * @return SelectBuilder
     */
    public function provideSelectBuilder($table);
}

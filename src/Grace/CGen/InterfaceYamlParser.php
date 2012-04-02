<?php
/**
 *
 * @author darthvader
 */
namespace Grace\CGen;

interface InterfaceYamlParser {
    public function __construct();
    public function getParseFile($filepath, $classname);
}
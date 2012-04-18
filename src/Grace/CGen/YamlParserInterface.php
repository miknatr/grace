<?php
/**
 *
 * @author darthvader
 */
namespace Grace\CGen;

interface YamlParserInterface {
    public function __construct();
    public function getParseFile($filepath, $classname);
}
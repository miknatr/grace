<?php
namespace Grace\CGen;

interface InterfaceCGenerator {
    /*
     * @param array $config
     * @param string $array
     */
    public function __construct();
    public function genYamlClass($dirYaml, $dirClasses, $className);
    public function parseYamlClass();
}

<?php
namespace Grace\CGen;

interface ClassGeneratorInterface {
    /*
     * @param array $config
     * @param string $array
     */
    public function __construct($dirYaml, $dirClasses, $className);
    public function generate();
    public function getVar($var);
    public function getGetter($fieldname);
    public function getSetter($fieldname);
}
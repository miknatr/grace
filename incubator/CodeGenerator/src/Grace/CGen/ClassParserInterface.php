<?php
namespace Grace\CGen;

interface ClassParserInterface
{
    //put your code here
    function __construct($classname, $outputDir, $additionalClass);
    function getClassName();
    function getClassFields();
    function getClassMethods();
    function getParentClassName();
    function getParentClassMethods();
    function getParentClassFields();
}
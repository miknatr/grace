<?php
namespace Grace\CGen;

interface ClassParserInterface {
    //put your code here
    function __construct($classname);
    function getClassName();
    function getClassFields();
    function getClassMethods();
    function getParentClassName();
    function getParentClassMethods();
    function getParentClassFields();
}
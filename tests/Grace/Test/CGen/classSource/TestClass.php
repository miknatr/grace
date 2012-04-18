<?php
namespace Grace\CGen;
//require './ParentTestConstruct.php';
include dirname(__FILE__) .'/ParentTestConstruct.php';

class TestClass extends ParentTestConstruct{
    public $childVar = "";

    public function __construct(){
        return $this;
    }
    
    public function getChildPublicInnerMethod($inpVar1,$inpVar2){
        //todo djigurdu
    }
    
    private function setChildPublicInnerMethod($inpVar1,$inpVar2){
        //todo djigurdu
    }
}
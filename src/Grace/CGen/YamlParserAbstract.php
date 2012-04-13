<?php
/**
 * Description of AbstractYamlParser
 *
 * @author darthvader
 */
namespace Grace\CGen;
require_once  '/home/darthvader/grace/vendors/symfony/src/Symfony/Component/Yaml/Yaml.php';
require_once  '/home/darthvader/grace/vendors/symfony/src/Symfony/Component/Yaml/Parser.php';
require_once  '/home/darthvader/grace/vendors/symfony/src/Symfony/Component/Yaml/Inline.php';
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
//TODO Add exceptions to all classes
abstract class YamlParserAbstract implements YamlParserInterface{
    public $parser = null;
    public function __construct(){
        $this->parser = new Parser();
        return $this;
    }

    public function getParseFile($filepath,$classname){
        $file =  $filepath."/".$classname.".yml";
        if (!file_exists($file)) {
            throw new \Exception("File does not exist");
        }
        try {
            $parsedFile = $this->parser->parse(file_get_contents($file));
            $this->parser = null;
            return $parsedFile;
        } catch (Symfony\Component\Yaml\Exception\ParseException $ex){
            printf("Unable to parse the YAML string: %s", $ex->getMessage());
            return FALSE;
        }
    }
}
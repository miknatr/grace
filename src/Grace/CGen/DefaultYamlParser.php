<?php
/**
 * Description of defaultYamlParser
 *
 * @author darthvader
 * @return array
 * @var array
 */
namespace Grace\CGen;
class DefaultYamlParser extends YamlParserAbstract {

    public function getParseFile($filepath,$classname){
        if ($classname!="*"){
            $file =  $filepath."/".$classname.".yml";
            if (!file_exists($file)) {
                throw new YamlParserExeption("File does not exist!");
            }
            try {
                $parsedFile = $this->parser->parse(file_get_contents($file));
                $this->parser = null;
                return $parsedFile;
            } catch (Symfony\Component\Yaml\Exception\ParseException $ex){
                printf("Unable to parse the YAML string: %s", $ex->getMessage());
                return FALSE;
            }
        }else{
            //parse all files in dir with *.yml
            $parsedFile = array();
            $scanDir = scandir($filepath);
            if (count($scanDir)<=2){
                throw new YamlParserExeption("Current directory is empty");
            }else{
                //aggregate from all YAML to one array
                $validFiles = $this->getYmlFiles($scanDir);
                for ($i=0; $i<count($validFiles); $i++){
                    $tmpArray = $this->parser->parse(file_get_contents($filepath."/".$validFiles[$i]));
                    $parsedFile = $this->appendToArray($parsedFile, $this->getChildNodes($tmpArray));
                }
                print_r($parsedFile);
                return $parsedFile;
            }
        }
    }
    
    //appending childs to array
    public function appendToArray($majorArray, $appendibleArray){
        foreach($appendibleArray as $key => $value){
            if (!isset($majorArray[$key])){
                $majorArray[$key] = $value;
            }
        }
        return $majorArray;
    }
    
    //gets .yml files from directory
    public function getYmlFiles($dirContent){
        $validFiles = array();
        for ($i=0; $i<count($dirContent); $i++){
            $tmpFile = explode(".", $dirContent[$i]);
            if ($tmpFile[(count($tmpFile)-1)]=="yml"){
                array_push($validFiles, $tmpFile[(count($tmpFile)-2)].".yml");
            }
        }
        return $validFiles;
    }
    
    //gets data of classes from array
    public function getChildNodes($YamlArray){
        print_r("\n\n");
        print_r(count($YamlArray));
        print_r("\n\n");
        foreach ($YamlArray as $key => $value){
            if (count($YamlArray)>0){
                $tmpArray = array();
                foreach($value as $classBlock => $classContent){
                    $tmpArray[$classBlock] = $classContent;
                }
                return $tmpArray;
            }
        }
    }
}
<?php

/**
 *  @file GPGColonParser.php
 *  @brief Colon parser for --list-keys and --list-secret-keys directive. Project home: https://github.com/vajayattila/GPGShell
 *	@author Vajay Attila (vajay.attila@gmail.com)
 *  @copyright MIT License (MIT)
 *  @date 2018.09.20-2019.07.18
 *  @version 1.0.1.2
 */

 class GPGColonParser{
    private $__structure;
    private $__lasttime;

    function __construct(){
        $structureJson=file_get_contents(__DIR__.'/GPGStructure.json');
        $this->__structure=json_decode($structureJson, true);
        //print_r($this->__structure);
    } 

    function recordTypeIs($type, $fields){
        return $fields[0]===$type;
    }
       
    private function findTypeStructure($type){
        $return=FALSE;
        $array=$this->__structure['typeOfRecord'];
        foreach($array as $item){
            foreach($item as $key=>$stru){
                if($key==$type){
                    $return=$stru;
                }
            }
        };
        return $return;
    }

    private function parse__($fields, $fieldcount, $caller){
        $structure=$this->findTypeStructure($fields[0]);
        $return=array(
            'description' => $structure["description"],
            'recordtype' => $fields[0], 
        );
        for( $i=0; $i<$fieldcount; ++$i ){
            if(
                array_key_exists('fields', $structure) &&
                array_key_exists($i, $structure['fields']) &&
                array_key_exists('description', $structure['fields'][$i]) &&
                isset($fields) && 
                isset($fields[$i])
            ){
                $return['fields'][]=array(
                    'description' => $structure['fields'][$i]['description'],
                    'value' => $fields[$i],
                    'fieldinfo' => $structure['fields'][$i]['possibleValues'],
                    'multipleFlags' => $structure['fields'][$i]['multipleFlags']?'true': 'false'
                );   
            }else{
                //user_error("Field index not found ($caller):".$i);
                break;
            }
        };
        return $return;
    }

    private function toUTCTime(&$return){
        if($return!=0){
            $return=gmdate("Y-m-d\TH:i:s\Z",$return);    
        }
        return $return;  
    }

    private function parsetru($fields){
        $return=$this->parse__($fields, 8, __FUNCTION__);
        $this->toUTCTime($return['fields'][3]['value']);
        $this->toUTCTime($return['fields'][4]['value']);       
        return $return;
    }

    private function parsepub($fields){
        $return=$this->parse__($fields, 21, __FUNCTION__); 
        $this->__lasttime=$return['fields'][5]['value']; 
        $this->toUTCTime($return['fields'][5]['value']);  
        $this->toUTCTime($return['fields'][6]['value']);        
        return $return;
    }    

    private function parsesig($fields){
        $return=array();//$this->parse__($fields, 21);      
        return $return;
    }      

    private function parsesec($fields){
        $return=$this->parse__($fields, 21, __FUNCTION__);  
        $this->__lasttime=$return['fields'][5]['value'];         
        $this->toUTCTime($return['fields'][5]['value']);  
        $this->toUTCTime($return['fields'][6]['value']);        
        return $return;
    }    
    
    private function parseuid($fields){
        $return=$this->parse__($fields, 21, __FUNCTION__);  
        //$this->__lasttime=$return['fields'][5]['value'];            
        $this->toUTCTime($return['fields'][5]['value']);  
        $this->toUTCTime($return['fields'][6]['value']);         
        return $return;
    } 
    
    private function parsesub($fields){
        $return=$this->parse__($fields, 21, __FUNCTION__);  
        $this->__lasttime=$return['fields'][5]['value'];            
        $this->toUTCTime($return['fields'][5]['value']);  
        $this->toUTCTime($return['fields'][6]['value']);         
        return $return;
    }     

    private function parsessb($fields){
        $return=$this->parse__($fields, 21, __FUNCTION__);  
        $this->toUTCTime($return['fields'][5]['value']);  
        $this->toUTCTime($return['fields'][6]['value']); 
        return $return;
    }     
    
    private function parsefpr($fields){
        $return=$this->parse__($fields, 21, __FUNCTION__);  
        $this->toUTCTime($return['fields'][5]['value']);  
        $this->toUTCTime($return['fields'][6]['value']); 
        return $return;
    }        

    private function key($fields){
        $return=$fields[5];
        switch($fields[0]){
            case 'pkd':
            case 'tfs':
            case 'tru':
            case 'spk':
            case 'cfg':
                $return=$fields[0];
                break;     
            case 'uid':              
            case 'fpr':
                $return=$this->__lasttime;
                //$this->__lasttime=NULL;
                break;
            default:
                // already set
        };
        return $return;
    }

    function isSpecialRecord($fields){
        $return=false;
        if(isset($fields[0])){
            switch($fields[0]){
                case 'pkd':
                case 'tfs':
                case 'tru':
                case 'spk':
                case 'cfg':
                    $return=true;
                    break;               
                default:
                    // already set
            };
        }
        return $return;
    }

    function getValueDescription($field){
        $return=FALSE;
        if(isset($field['value'])&&isset($field['fieldinfo'])){
            $value=$field['value'];
            foreach($field['fieldinfo'] as $item){
                if($item['value']==$value){
                    $return=$item['description'];
                    break;
                };
            };
        };
        return $return;
    }

    function parseOutput($output){
        $return = array();
        print_r($output);
        foreach($output as $line){
            if(is_array($line)){
                print_r($line);
            }
            $fields=explode(':', $line);
            $methodname='parse'.$fields[0];
            if(method_exists ( $this , $methodname )){
                $return[$this->key($fields)][]=
                    call_user_func_array(array(__CLASS__, $methodname), array($fields));
            };
        };
        return $return;
    }

 }

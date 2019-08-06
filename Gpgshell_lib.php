<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  @file Gpgshell_lib.php
 *  @brief GPGShell library for CodeIgniter. Project home: https://github.com/vajayattila/GPGShell
 *	@author Vajay Attila (vajay.attila@gmail.com)
 *  @copyright MIT License (MIT)
 *  @date 2018.09.26-2019.08.06
 *  @version 1.0.2.2
 */

require_once(realpath(__DIR__.'/GPGShell.php'));

class Gpgshell_lib{
    private $CI;
    private $gpg;
    
    function __construct(){
        $this->CI = &get_instance();
        $this->gpg=new GPGShell(realpath(__DIR__.'/.gnupg'));
    }

    function listKeys($keyid=NULL){
        return $this->gpg->listKeys($keyid);
    }

    function listSecretKeys(){
        return $this->gpg->listSecretKeys();
    }

    function exportSecretKey($userid){
        return $this->gpg->exportSecretKey($userid);
    }

    function exportKey($userid){
        return $this->gpg->exportKey($userid);
    }

    function importKey($key){
        return $this->gpg->importKey($key);
    }

    function encrypt($userid, $data){
        return $this->gpg->encrypt($userid, $data);
    }

    function decrypt($userid, $pgpdata){
        return $this->gpg->decrypt($userid, $pgpdata);
    }

    function detachSign($fileName, $sigName, $userid){
        return $this->gpg->detachSign($fileName, $sigName, $userid);
    }

    function verify($fileName, $sigName, $userid){
        return $this->gpg->verify($fileName, $sigName, $userid);
    }

    function getExitCode(){
        return $this->gpg->exitCode; 
    }

	public function listPackets($pgpdata, &$output){
        return $this->gpg->listPackets($pgpdata, $output);
    }    

    public function addressedUserId($pgpdata, &$output){
        $return=$this->listPackets($pgpdata, $output);
        if($return!==FALSE){            
            if(count($output)>=2){  
		// find keyid line
		$linepos=false;
		foreach($output as $linepos=>$line){
			if(strpos($line, 'keyid')!==FALSE){
				break;
			}
		}
                $line=explode("\n", $output[$linepos]);          
                $keyidarr=explode(" keyid ", $line[0]);
                $keyid=$keyidarr[1];
                $return=$this->listKeys($keyid);
                if($return!==FALSE){
                    foreach($this->gpg->output as $key=>$rec){
                        if(!$this->gpg->isSpecialRecord($key)&&'tru'!=$key){ 
                            foreach($rec as $item){	
                                switch($item['recordtype']){
                                    case 'uid':
                                        $uid=$item['fields'][9];
                                        $output=$uid['value'];      																			
                                        break;
                                    case 'pub':
                                        $pub=$item['fields'][9];
                                        $output=$pub['value'];  
																				//log_message('debug', __FUNCTION__.'.output='.print_r($output, true));																					
                                        break;																				
                                }
                            }
                        }
                    }     
                }
            }else{
                $return=FALSE; 
            }
        }
        return $return;
    }
		
	public function getEmailFromUserid($userid){
		$return=false;
		if(preg_match('/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/', $userid, $maches)===1){
			$return=$maches[0];
		}
		return $return;
	}

}

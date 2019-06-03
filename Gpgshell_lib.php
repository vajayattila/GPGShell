<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  @file Gpgshell_lib.php
 *  @brief GPGShell library for CodeIgniter. Project home: https://github.com/vajayattila/GPGShell
 *	@author Vajay Attila (vajay.attila@gmail.com)
 *  @copyright MIT License (MIT)
 *  @date 2018.09.26-2019.06.03
 *  @version 1.0.1.1
 */

require_once(realpath(__DIR__.'/GPGShell.php'));

class Gpgshell_lib{
    private $CI;
    private $gpg;
    
    function __construct(){
        $this->CI = &get_instance();
        $this->gpg=new GPGShell(realpath(__DIR__.'/.gnupg'));
    }

    function listKeys(){
        return $this->gpg->listKeys();
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
            $lines=explode("\n", $output);
            if(count($lines)>=2){
                $line=explode("\"", $lines[1]);
                if(count($line)>=3){
                    $output=$line[1];    
                }else{
                    $return=FALSE;    
                }
            }else{
                $return=FALSE; 
            }
        }
        return $return;
    }

}

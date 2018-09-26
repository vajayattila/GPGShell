<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  @file Gpgshell_lib.php
 *  @brief GPGShell library for CodeIgniter. Project home: https://github.com/vajayattila/GPGShell
 *	@author Vajay Attila (vajay.attila@gmail.com)
 *  @copyright MIT License (MIT)
 *  @date 2018.09.26-2018.09.26
 *  @version 1.0.0.0
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

}

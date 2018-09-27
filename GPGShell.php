<?php

/**
 *  @file GPGShell.php
 *  @brief Interface for gpg (GnuPG) shell commands. Project home: https://github.com/vajayattila/GPGShell
 *	@author Vajay Attila (vajay.attila@gmail.com)
 *  @copyright MIT License (MIT)
 *  @date 2018.09.19-2018.09.26
 *  @version 1.0.0.0
 * 
 *  Features:
 * 	--list-keys
 *  --list-secret-keys
 *  --export
 *  --export-secret-key
 *  --import
 *  --encrypt
 *  --decrypt
 *  --detach-sign
 *  --verify
 */

require_once(__DIR__.'/GPGColonParser.php');

 // Interface class for gpg (GnuPG) shell commands
class GPGShell{
	// @brief GnuPG data home directory
	private $__homedir;
	// @brief Last output	
	private $__output;
	// @brief Last exit code	
	private $__exitcode;
	// @brief Colon parser
	private $__colonParser;
	
	// @brief Constructor with an optional homedir parameter
	function __construct(){
		$this->__colonParser=new GPGColonParser();
		$i = func_num_args();		
		if($i===1){
			$a = func_get_args();
			$this->__homedir=$a[0];	// homedir parameter
		} else {
			$this->__homedir=__DIR__;	// default homedir parameter
		}
	}
	
	private function run($cmd)
	{
		$result = null;
		exec($cmd, $this->__output, $result);
		$this->__exitcode=$result;
		// Return true on successfull command invocation
		return $result === 0;
	}

	private function makeArgs()
	{
		$args = [
			'--batch',			
			'--quiet',
			'--homedir',
			$this->homedir,
			'--local-user www-data'
		];
		return $args;
	}

	private function parseListKeys($output){
		$retval=$output;
		//print_r($output);
		return $retval;
	}

	function isSpecialRecord($key){
		return $this->__colonParser->isSpecialRecord($key);
	}

	function getValueDescription($field){
		return $this->__colonParser->getValueDescription($field);
	}
	
	function __get($name) {
		if($name === 'exitCode'){
			return $this->__exitcode;
		} else if($name === 'output'){ 
			return $this->__output;			
		} else if($name === 'homedir'){ 
			return $this->__homedir;				
		} else {
			user_error("Invalid property: " . __CLASS__ . "->$name");
		}
	}
	
	function __set($name, $value) {
		if($name === 'homedir'){
			$this->__homedir=$value;
		}{
			user_error("Can't set property: " . __CLASS__ . "->$name");
		}
	}	
	
	// @brief List keys
	function listKeys()
	{
		$this->__output=NULL;
		$args = $this->makeArgs();				
		$args[] = '--list-keys';
		$args[] = '--with-colons';		
		$cmd = sprintf('gpg %s ', implode(' ', $args));
		$retval=$this->run($cmd);
		$this->__output=$this->__colonParser->parseOutput($this->output);
		return $retval;
	}

	// @brief List Secret keys
	function listSecretKeys()
	{
		$this->__output=NULL;
		$args = $this->makeArgs();		
		$args[] = '--list-secret-keys';
		$args[] = '--with-colons';		
		$cmd = sprintf('gpg %s ', implode(' ', $args));
		$retval=$this->run($cmd);
		$this->__output=$this->__colonParser->parseOutput($this->output);
		return $retval;
	}	

	// @brief Export private key
	function exportSecretKey($userid){
		$this->__output=NULL;
		$args = $this->makeArgs();		
		$args[] = '--armor';	
		$args[] = '--export-secret-key';
		$args[] = $userid;				
		$cmd = sprintf('gpg %s ', implode(' ', $args));
		return $retval=$this->run($cmd);		
	}

	// @brief Export public key
	function exportKey($userid){
		$this->__output=NULL;
		$args = $this->makeArgs();			
		$args[] = '--armor';	
		$args[] = '--export';
		$args[] = $userid;				
		$cmd = sprintf('gpg %s ', implode(' ', $args));
		return $retval=$this->run($cmd);		
	}

	// @brief Import public or private key 
	function importKey($key){
		$return=false;
		$filename=__DIR__.'/'.uniqid().'.tmp';
		if(file_put_contents($filename, $key)!==FALSE){
			$this->__output=NULL;
			$args = $this->makeArgs();			
			$args[] = '--armor';	
			$args[] = '--import';
			$args[] = $filename;				
			$cmd = sprintf('gpg %s ', implode(' ', $args));
			$return=$this->run($cmd);	
			unlink($filename);					
		}
		return $return;
	}

	// @brief Encrypt string
	function encrypt($userid, $data){
		$return=false;
		$infilename=__DIR__.'/'.uniqid().'_in.tmp';
		$outfilename=__DIR__.'/'.uniqid().'_out.tmp';
		if(file_put_contents($infilename, $data)!==FALSE){
			$this->__output=NULL;
			$args = $this->makeArgs();		
			$args[] = '--armor';	
			$args[] = '--encrypt';
			$args[] = '--output';
			$args[] = $outfilename;
			$args[] = '--recipient';
			$args[] = $userid;
			$args[] = $infilename;
			$cmd = sprintf('gpg %s ', implode(' ', $args));
			$return=$this->run($cmd);
			if($return!==FALSE){	
				$return=file_get_contents($outfilename);
				unlink($outfilename);
			}
			unlink($infilename);					
		}
		return $return;
	}	

	// @brief Decrypt pgp_message
	function decrypt($userid, $pgpdata){
		$return=false;
		$infilename=__DIR__.'/'.uniqid().'_in.tmp';
		$outfilename=__DIR__.'/'.uniqid().'_out.tmp';		
		if(file_put_contents($infilename, $pgpdata)!==FALSE){
			$this->__output=NULL;
			$args = $this->makeArgs();		
			$args[] = '--armor';	
			$args[] = '--decrypt';
			$args[] = '--output';
			$args[] = $outfilename;
			$args[] = $infilename;
			$cmd = sprintf('gpg %s ', implode(' ', $args));		 
			$return=$this->run($cmd);
			if($return!==FALSE){	
				$return=file_get_contents($outfilename);
				unlink($outfilename);
			}
			unlink($infilename);					
		}
		return $return;
	}	

	// @brief Create sign file for a file
    public function detachSign($fileName, $sigName, $userid)
    {
		$args = array();
		$args[] = '--detach-sign';
		$args[] ='--armor';		
		$args[] = '--batch';
		$args[] = '--quiet';
		$args[] = '--yes';						
		$args[] = '--output ';
		$args[] = $sigName;
		$args[] ='--recipient';	
		$args[] = $userid;		
		$args[] = $fileName;
		$cmd = sprintf('gpg %s', implode(' ', $args));
        return $this->run($cmd);
	}	
	
	// @brief Verify sign file
	public function verify($fileName, $sigName, $userid)
    {
        $args = array();
		$args[] = '--batch';
		$args[] = '--quiet';
		$args[] = '--recipient';	
		$args[] = $userid;
		$args[] = '--verify';							
		$args[] = $sigName;
		$args[] = $fileName;		
		$cmd = sprintf('gpg %s', implode(' ', $args));		
        return self::run($cmd);
    }	

}

// EOF - GpgShell.php

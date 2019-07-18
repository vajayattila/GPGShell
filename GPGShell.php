<?php

/**
 *  @file GPGShell.php
 *  @brief Interface for gpg (GnuPG) shell commands. Project home: https://github.com/vajayattila/GPGShell
 *	@author Vajay Attila (vajay.attila@gmail.com)
 *  @copyright MIT License (MIT)
 *  @date 2018.09.19-2019.07.18
 *  @version 1.2.0.3
 * 
 *  Features:
 * 		--list-keys
 *  	--list-secret-keys
 *  	--export
 *  	--export-secret-key
 *  	--import
 *  	--encrypt
 *  	--decrypt
 *  	--detach-sign
 *  	--verify
 *  	--list-packets
 * 		--import-ownertrust
 *  	--gen-key
 * 	Special features:
 *  	getKeyFingerprintsByEmail
 *  	getSecretKeyFingerprintsByEmail
 * 		deleteSecretKeyByFingerprint
 * 		deleteKeyByFingerprint
 * 		deleteAllSecretKeyByEmail
 * 		deleteAllKeyByEmail
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
	// @brief gpg exe name
	private $__gpg_exe_name;
	
	// @brief Constructor with an optional homedir parameter
	function __construct(){
		$this->__colonParser=new GPGColonParser();
		$this->__gpg_exe_name="gpg";
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
		} else if($name === 'gpg_exe_name'){ 
			return $this->__gpg_exe_name;				
		} else {
			user_error("Invalid property: " . __CLASS__ . "->$name");
		}
	}
	
	function __set($name, $value) {
		if($name === 'homedir'){
			$this->__homedir=$value;
		}else if($name === 'gpg_exe_name'){
			$this->__gpg_exe_name=$value;
		}else{
			user_error("Can't set property: " . __CLASS__ . "->$name");
		}
	}	
	
	// @brief List keys
	function listKeys($keyid=NULL)
	{
		$this->__output=NULL;
		$args = $this->makeArgs();				
		$args[] = '--list-keys';
		$args[] = '--with-colons';	
		$args[] = '--with-fingerprint';			
		if($keyid!==NULL){
			$args[] = $keyid;	
		}
		$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));
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
		$args[] = '--with-fingerprint';	
		$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));
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
		$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));
		return $retval=$this->run($cmd);		
	}

	// @brief Export public key
	function exportKey($userid){
		$this->__output=NULL;
		$args = $this->makeArgs();			
		$args[] = '--armor';	
		$args[] = '--export';
		$args[] = $userid;				
		$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));
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
			//$args[] = '2>&1';	
			$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));
			$return=$this->run($cmd);	
			//echo 'importKey->'.print_r($this->output, true).'<br>';	
			unlink($filename);					
		}
		return $return;
	}

	// @brief Import ownertrust
	function importOwnertrust($otfilename){
		$return=false;
		$args = array();	
		$args = $this->makeArgs();				
		$args[] = '--import-ownertrust';	
		$args[] = $otfilename;	
		//$args[] = '2>&1';						
		$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));
		$return=$this->run($cmd);
		return $return;
	}	

	/* @brief genKey
	 * @param [in] $keylength	The requested length of the generated key in bits.
	 * @param [in] $nameReal	Real name
	 * @param [in] $nameEmail	email
	 * @param [in] $expireDate	Set the expiration date for the key (and the subkey). It may either be entered in ISO date format (e.g. "20000815T145012") or as number of days, weeks, month or years after the creation date. The special notation "seconds=N" is also allowed to specify a number of seconds since creation. Without a letter days are assumed. Note that there is no check done on the overflow of the type used by OpenPGP for timestamps. Thus you better make sure that the given value make sense. Although OpenPGP works with time intervals, GnuPG uses an absolute value internally and thus the last year we can represent is 2105. 
	 * @param [in] $Passphrase	NULL or Passphrase
	 */
	function genKey($keylength, $nameReal, $nameEmail, $expireDate, $Passphrase){
		$return=false;
		$scriptfilename=__DIR__.'/'.uniqid().'_script.tmp';
		$content=
			"Key-Type: 1\nKey-Length: $keylength\nSubkey-Type: 1\nSubkey-Length: $keylength\n".
			"Name-Real: $nameReal\nName-Email: $nameEmail\nExpire-Date: $expireDate\n".
			($Passphrase===NULL?"%no-protection\n":"Passphrase: $Passphrase\n");
		file_put_contents($scriptfilename,$content);
		$args = $this->makeArgs();		
		$args[] = '--gen-key';	
		$args[] = $scriptfilename;				
		$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));
		$return=$this->run($cmd);
		//print_r($cmd."\n");
		//print_r($content."\n");		
		unlink($scriptfilename);		
		return $return;
	}

	/* @brief Get public key's fingerprints by email */
	function getKeyFingerprintsByEmail($nameEmail){
		$retval=FALSE;
		$record=array();
		if($this->listKeys()){
			foreach($this->output as $key=>$rec){
				if(!$this->isSpecialRecord($key)&&'tru'!=$key){
					foreach($rec as $item){		
						//print_r( $item);										
						switch($item['recordtype']){
							case 'pub':
								$r=$item['fields'][4];
								$record=array(
									$r['description']=>$r['value']
								);								
								//print_r($item['fields'][4]);
								break;
							case 'fpr':																												
								if($record!==null){
									$r=$item['fields'][9];
									$record[$r['description']]=$r['value'];
								}	
								break;				
							case 'uid':
								if($record!==null){
									$r=$item['fields'][9];
									$userid=$r['value'];
									if(strpos($userid, "<$nameEmail>")){
										$record[$r['description']]=$userid;									
										$retval[]=$record;
									}
									$record=null;		
								}
								//print_r($item['fields'][9]);									
								break;							
						}
					}
				}
			}
		}
		//print_r($retval);
		return $retval;
	}

	/* @brief Get private key's fingerprints by email */
	function getSecretKeyFingerprintsByEmail($nameEmail){
		$retval=FALSE;
		$record=array();
		if($this->listSecretKeys()){
			foreach($this->output as $key=>$rec){
				if(!$this->isSpecialRecord($key)&&'tru'!=$key){
					foreach($rec as $item){		
						//print_r( $item);															
						switch($item['recordtype']){
							case 'sec':
								$r=$item['fields'][4];
								$record=array(
									$r['description']=>$r['value']
								);								
								//print_r($item['fields'][4]);
								break;
							case 'fpr':																												
								if($record!==null){
									$r=$item['fields'][9];
									$record[$r['description']]=$r['value'];
									//print_r($record);
								}	
								break;				
							case 'uid':
								if($record!==null){
									$r=$item['fields'][9];
									$userid=$r['value'];
									if(strpos($userid, "$nameEmail")){
										$record[$r['description']]=$userid;									
										$retval[]=$record;
									}
									$record=null;		
								}
								//print_r($item['fields'][9]);									
								break;							
						}
					}
				}
			}
		}
		//echo 'getSecretKeyFingerprintsByEmail->'.print_r($retval, true).'<br>';
		return $retval;
	}	
	
	// @brief Delete private key by fingerprint
	function deleteSecretKeyByFingerprint($fingerprint){
		$args = $this->makeArgs();		
		$args[] = '--yes';		
		$args[] = '--delete-secret-key';	
		$args[] = $fingerprint;				
		//$args[] = '2>&1';	
		$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));
		$return=$this->run($cmd);
		//echo 'deleteSecretKeyByFingerprint->'.print_r($this->output, true).'<br>';
		return $return;
	}

	// @brief Delete private key by fingerprint
	function deleteKeyByFingerprint($fingerprint){
		$args = $this->makeArgs();		
		$args[] = '--yes';		
		$args[] = '--delete-key';	
		$args[] = $fingerprint;		
		//$args[] = '2>&1';			
		$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));
		$return=$this->run($cmd);
		//echo 'deleteKeyByFingerprint->'.print_r($this->output, true).'<br>';		
		return $return;
	}	

	// @brief Delete all private key by email	
	function deleteAllSecretKeyByEmail($nameEmail){
		$return=true;
		$keys=$this->getSecretKeyFingerprintsByEmail($nameEmail);
		if(isset($keys)&&is_array($keys)){
			foreach($keys as $item){
				if($return===true){
					$return=$this->deleteSecretKeyByFingerprint($item['Fingerprint']);
				}
			}
		}
		return $return;
	}

	// @brief Delete all public key by email	
	function deleteAllKeyByEmail($nameEmail){
		$return=true;
		$keys=$this->getKeyFingerprintsByEmail($nameEmail);
		if(isset($keys)&&is_array($keys)){		
			foreach($keys as $item){
				if($return===true){
					$return=$this->deleteKeyByFingerprint($item['Fingerprint']);
				}
			}
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
			$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));
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
			$cmd = sprintf($this->__gpg_exe_name.' %s ', implode(' ', $args));		 
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
		$args = $this->makeArgs();			
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
		$cmd = sprintf($this->__gpg_exe_name.' %s', implode(' ', $args));
        return $this->run($cmd);
	}	
	
	// @brief Verify sign file
	public function verify($fileName, $sigName, $userid)
    {
		$args = $this->makeArgs();		
		$args[] = '--batch';
		$args[] = '--quiet';
		$args[] = '--recipient';	
		$args[] = $userid;
		$args[] = '--verify';							
		$args[] = $sigName;
		$args[] = $fileName;		
		$cmd = sprintf($this->__gpg_exe_name.' %s', implode(' ', $args));		
        return self::run($cmd);
	}	
	
	public function listPackets($pgpdata, &$output){
		$return=false;		
		$filename=__DIR__.'/'.uniqid().'.tmp';
		if(file_put_contents($filename, $pgpdata)!==FALSE){
			$args = $this->makeArgs();		
			if (($key = array_search("--quiet", $args)) !== false) { // remove --quiet
				unset($args[$key]);
			}			
			$args[] = '--batch';
			$args[] = '--list-packets';			
			$args[] = $filename;	
			$cmd = sprintf($this->__gpg_exe_name.' %s', implode(' ', $args));	
			$return=self::run($cmd);		
			if($return!==FALSE){
				$output=$this->output;
			}				
			unlink($filename);
		}
		return $return;
	}
		

}

// EOF - GpgShell.php

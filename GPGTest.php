<?php

/**
 *  @file GPGTest.php
 *  @brief Interface for gpg (GnuPG) shell commands test cases. Project home: https://github.com/vajayattila/GPGShell
 *	@author Vajay Attila (vajay.attila@gmail.com)
 *  @copyright MIT License (MIT)
 *  @date 2018.09.19-2019.07.16
 *  @version 1.0.0.0
 * 
 *  Testing:
 * 	--list-keys
 *  --list-secret-keys
 *  --export
 *  --export-secret-key
 *  --import
 *  --encrypt
 *  --decrypt
 *  --detach-sign
 *  --verify
 *  --gen-key
 *  getKeyFingerprintsByEmail
 * 	getSecretKeyFingerprintsByEmail
 * 	deleteSecretKeyByFingerprint
 *  deleteKeyByFingerprint
 * 	deleteAllSecretKeyByEmail
 * 	deleteAllKeyByEmail
 */

require_once (__DIR__."/GPGShell.php");

$gpg=new GPGShell(__DIR__.'/.gnupg');

function stringwidth($str, $width, $align="left"){
	if($align=="left"){
		for($i=strlen($str);$i<=$width;$i++){
			$str.=' ';
		}
	}else{
		for($i=strlen($str);$i<=$width;$i++){
			$str=' '.$str;
		}		
	}
	return $str;
}

function printField($gpg, $field){
	// print stringwidth($name, 30).' = '. stringwidth($field['value'], 30);
	print stringwidth($field['description'], 30, 'right').' = '. stringwidth($field['value'], 30);
	$desc=$gpg->getValueDescription($field);
	if($desc!=NULL){
		print ' '.$desc;
	}
	print "\n";
} 

function printPub($gpg, $item){
	print stringwidth($item['description'], 30)."\n";		
	printField($gpg, $item['fields'][1]); // validity
	printField($gpg, $item['fields'][2]); // keylength	
	printField($gpg, $item['fields'][3]); // algorithm		
	printField($gpg, $item['fields'][4]); // keyid		
	printField($gpg, $item['fields'][5]); // creation
	printField($gpg, $item['fields'][6]); // expiration
	printField($gpg, $item['fields'][11]); // keycaps
	printField($gpg, $item['fields'][17]); // compilanceflags
}

function printSec($gpg, $item){
	print stringwidth($item['description'], 30)."\n";
	printField($gpg, $item['fields'][1] ); // validity
	printField($gpg, $item['fields'][2] ); // keylength		
	printField($gpg, $item['fields'][3] ); // algorithm	
	printField($gpg, $item['fields'][4] ); // keyid	
	printField($gpg, $item['fields'][5]);  // creation
	printField($gpg, $item['fields'][6]);  // expiration
	printField($gpg, $item['fields'][11]); // keycaps
	printField($gpg, $item['fields'][14]); // snofatoken
	printField($gpg, $item['fields'][17]); // compilanceflags	
}

function printRecords($gpg){
	$showFingerprint=true;
	foreach($gpg->output as $key=>$rec){
		if(!$gpg->isSpecialRecord($key)&&'tru'!=$key){
			foreach($rec as $item){
				switch($item['recordtype']){
					case 'sec':
						$showFingerprint=true;
						printSec($gpg, $item);
						break;
					case 'pub':
						$showFingerprint=true;
						printPub($gpg, $item);
						break;
					case 'fpr':
						if($showFingerprint===true){
							$fpr=$item['fields'][9];
							printField($gpg, $fpr);	
							$showFingerprint=false;		
						}									
						break;
					case 'uid':
						$uid=$item['fields'][9];
						printField($gpg, $uid);						
						break;
					default:
						//
				}
			};
			print("\n");			
		};
	};
}

function printListKeys($gpg){
	if($gpg->listKeys()){
		printRecords($gpg);		
	} else {
		echo('Exit code: '.$gpg->exitCode);
	}
}

function printListSecretKeys($gpg){
	if($gpg->listSecretKeys()){
		printRecords($gpg);		
	} else {
		echo('Exit code: '.$gpg->exitCode);
	}
}

function printSecretKey($gpg, $userid){
	if($gpg->exportSecretKey($userid)===true){
		echo implode("\n", $gpg->output);
	}else{
		echo('Exit code: '.$gpg->exitCode);		
	}
}

function printKey($gpg, $userid){
	if($gpg->exportKey($userid)===true){
		echo implode("\n", $gpg->output);
	}else{
		echo('Exit code: '.$gpg->exitCode);		
	}
}

function printEncrypt($gpg, $userid, $text){
	$return=$gpg->encrypt($userid, $text);
	if($return===FALSE){
		echo('Exit code: '.$gpg->exitCode);
	}else{
		echo($return);
	}
	return $return;
}

function printDecrypt($gpg, $userid, $pgpmessage){
	$return=$gpg->decrypt($userid, $pgpmessage);
	if($return===FALSE){
		echo('Exit code: '.$gpg->exitCode);
	}else{
		echo($return);
	}
	return $return;	
}

// Test cases
//----------------------------------------------------
// Test of --gen-key command
//$gpg->genKey(2048, 'GPGShell test keys', 'gpgshell@test.hu', 0, NULL);
//$gpg->genKey(2048, 'GPGShell test keys 2', 'gpgshell@test2.hu', 0, NULL);
//$gpg->genKey(2048, 'GPGShell test keys 3', 'gpgshell@test3.hu', 0, NULL);

// Test of getFingerprintsByEmail
//$gpg->getKeyFingerprintsByEmail('gpgshell@test.hu');
//$gpg->getSecretKeyFingerprintsByEmail('gpgshell@test.hu');

// Test of deleteSecretKeyByFingerprint
//$gpg->deleteSecretKeyByFingerprint('3C1FC03AE8BBBDBE561464853253DF9A0244E3DD');
//$gpg->deleteAllSecretKeyByEmail('gpgshell@test.hu');

// Test of --list-keys command
printListSecretKeys($gpg);

// Test of deleteKeyByFingerprint
//$gpg->deleteKeyByFingerprint('8EADE9526A9599F48B1A69E20F646DD8E2494AB9');
//$gpg->deleteAllKeyByEmail('gpgshell@test.hu');

// Test of --list-keys command
printListKeys($gpg);

// Test of --export-secret-key
//printSecretKey($gpg, 'test@gpgshell.example');

// Test of --export-key
// printKey($gpg, 'test@gpgshell.example');

// Test of --import (import@test public key)
/*$gpg->importKey('-----BEGIN PGP PUBLIC KEY BLOCK-----
Version: OpenPGP.js v3.0.13
Comment: https://openpgpjs.org

xsFNBFup6GUBEADJvx6lJgFlCnpN/mkHjYy3B9KSG/mJWJkeSQ6UEzJbrfQN
5FsMVBAuyTwUR6NuxhTVWXsRpVV974qjed/n/6vnwflQawXLEIvmP3GWPkV+
DiTmzsxb2ETPsLmw0eJbMsFDtzMjoRfwth0DpBlGBfIIm5G6ChtYm3X6LE3w
EVF7URHJU/xOHr7X+/qyj5U5OZ0jQurUN2YyGFRwN1VUllEzPuS28txA8QaY
s2uZICtoZPWycsscvBK8OIteLKTQriRkKb6yvw/66bp+alwG5DGTfjrj0GQR
1Dofb22T1sURrcLZaC9k1PG2/qCP/TsSeZykWX5+vsdsfATQ2um86fYGhC1X
Sertt+NuhFe24V3hiXxt1j0a4wWUGz+gIq6Ur10S8zLpiZ6GYOfFJ8o0u6pu
Nrfr0+bPIttNHCY+Uiaye9rsREqeWLB/idCqLok+xsi9sqM5Z+8LKyY2IcPr
vG4AyTZX8vSpdB7nB2LyYIM/uI/2pp4++gY+HJSrfgo6Lz/n9sEZ0oZKtuH0
socp206aXL4eJFP3AjZ7+Hpu/n3RSZAZbxCcDhtzFvlmLeGdSBw7s9ein4DU
gzwAH/iiJVYBPurh3YbCOvCkf9L0xvxEchB+CIRs3nHCZfOtpnGARtqUHDEz
AsIUSMQeygYrg1/U/yt/+wlD9FD2g3ktEXQfnwARAQABzQ0iaW1wb3J0QHRl
c3QiwsF1BBABCAApBQJbqehlBgsJBwgDAgkQzsiOeMouUJUEFQgKAgMWAgEC
GQECGwMCHgEAAAhgD/46Aub4autz+VG58aG9sGWds0gB9tpUo8Lch4xpy34r
BoD1HSQfUFUzJ5SDh/b2pZqR+UmE71A4sQz8nTQG8oqGTdsLbuyoDA/mnqA7
TEM4U7Jn/J6noWIt1rCYNx27tsp6VJj4cqdrjbwP7url3/PJPfPmLnlzP842
65iDejBxnz9+gkPSP3LDcw51M81Qt6zXwnlLRzYDBovZFLDmzSHmQJUIRJOe
JPcqfDmqjIASpmFtpHf2Ci/a7bnft13fgHxXtIFtEwzqUQWZfGyVIyjD8ybw
HtMIUuSTjcEzklPFc1xrcTTEJv2bEshq7cM9COAyA9O9WHnMGQX70iJjOJLq
AWf+GHWKm/ejDRWB9n2F/BQc/u/kniJvclV7VyxrS4hkwdp1QIi0YqC05ia8
ycvS+dPlp7vD9sV/K70LceIKbIGqCXXEkOTOGkbpYEM09PHBLXngOKhykCcE
+xQH/GuSBW+dF3ohz8as+2eh8t5WCdQTs7Jl5i6tIotAe4JFjjri3Qjxu+C+
+zru4D1NBExEmgyg9qkFvjiNbCUr+wn0N+21HjSkIQm1I/vqD1VAbI7YzSk/
V6BVuJI8t0lxv5lkzBlzE7ydSqZ3sIgQgOtgS/S1SHiTcbnMerQjxbwwJThv
qUnriMa9ImUiISNmZZOMQ/XqsBoofS65dc3fZ4XnR87BTQRbqehlARAA8fSX
HOn/eYhE8/SZ+DokO1p24C9e7DsjGXIn+h/muyAV5OcA+JcA3Ko0BRS+vkIs
Uq9NDoj5Vwz4aBPwkOMHAD8cJLioK4EP1nx7IxriL0mkgcEL5VU/lWbtj8rT
5e9V3NKpjVtQmXSiq9lcmONhyl6GrcTkc/hgakkGlTs4EgRr+RDCfkoOyHSA
RwNtJ/2TNMDDoezIzC9Dt0kJ/CXCL+4u+UwYRM+Mml7hbGUKUxMkEaANmnCK
zwwDnN4+ekCCdVN4nACZ5kuWkcWVPg6Hp5SE6PHp6iIfD2deELP9uUvBey41
JdSX9GMLQ4fb+RsX6I+z/7VfnWCeL3F3GzBNTlHZfTEhb4o4m+7qE7qL9i+h
sl5e0FLq/2JV2No/9kSgP6VqmEoenJsZR5Do3HKN0X13Ohnhyps7jkK5/b6+
7wVwWE8SC7T+Z7IEpXPjA9nAGpV2HtgLDH5ogfE8NIeC4XFF+hmy/rVmQjvM
PMFcK5RYanM9goil36cpeiGGTu8Qwwt2k2sUfJMJD7eCrxtJAEfkXIAavU+9
H0I0rTvZRv7g7WXB7lYPNubSjyjeOZ7wJSkwZ5elVMwl5l2cHvAjrl5zvria
h+rpbM8GKVLDNJGz3iQrFAkRuT0HWTnrJaIITqerTHUNuwmLkO8xX4edeO+n
d3nYPXvQMGxPvTHwiZsAEQEAAcLBXwQYAQgAEwUCW6noZQkQzsiOeMouUJUC
GwwAAF9oD/kBnplsX1ENRC3sTkXp1UK5VX/vIxO5IJWM2oGtDM+4fO9Z16g6
9DaL8R2SArzqmaaRZMRLfZZC4/kZyBvX63VV4pfaCVZ4FkHeUuraAXpacMdZ
xIry9QC0ws86rc4W3bapHU6nmcMlqqklhDlxtljXqNUc63x+4+U/2oCMlCLN
hVu0SmmbnAu+6ek4+l6VjpzMt/k9EthRgBB/fgt0NbNKJqoUzU+CbD/X2rGj
iCVJOnbIkvSG/k4LnvaRMcw1BhFoC5MKf5KKojrzsQ8BSs1AgRaaJd3GF0Qt
kJp8fdblvxAhSafV6OSd9MKyZW+LUHWSkLHQ3O42kWGaExDJZrLHJ3isxhlf
PTBhgIk4ls8obSugSPyet4GnV4kS4gpBeOBUvwcs7Pv8N5Fp4Dgx0kUc5ot3
tLzT4Aftd+UQyQPodKM8qQGovSRoaKUqIknKC6EmEXjeij+Z65wbN3H6DMbh
ZZ116evBuxStTizeW/X8SvGAWDaonLuCHsGAN5UGhpSRsqWv+Ge/z8vfKbEc
6jkRhYo2hIM1tHbcWNmGGnaryFaZrhEYcwBm9GImjigjrNftbS5XmjAG0RQp
Ku2uUpxc6MjvGiz/iHpGVWYwW4I1Ut/gTJh4qf+/f+VvwBMGXwFsEVGhX0bH
vCC83dAStjEaELaoLqjp1vwesfw8EXdLIA==
=c5EJ
-----END PGP PUBLIC KEY BLOCK-----

');*/
/*
$gpg->importKey('-----BEGIN PGP PRIVATE KEY BLOCK-----
Version: OpenPGP.js v3.0.13
Comment: https://openpgpjs.org

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
=K6B/
-----END PGP PRIVATE KEY BLOCK-----
');*/

// Test of --encrypt
//printEncrypt($gpg, 'import@test', 'Hello GPGShell!');

// Test of --decrypt
/*printDecrypt($gpg, 'import@test', '-----BEGIN PGP MESSAGE-----

hQIMAzKV5v1wKZItARAAtHUZ/5x4hqKmoek3lx/E+UJ6Hv/LvpAA/g9FmEaFaY5i
LNnrxgqXdKm2oy7ej05KF1wbfAcP3XjbXkRnu8Q8R+rgk9ukZqAwhGtdt3cULuHA
BLUDVVT3U91S9Na5pvq0mJXyUEB6oupXVssQDaCTTAF90kUyGJfUn3PyfzbzR6wk
ahAAqfFAMEhwvbw36t9zFLm27PgwEDsrWr1OFgLgCDxFs2Kl4PrlGBbtiz/h0few
1eq/jxyk/c7Y27cQW5SwRpd63h/suPcRG1A6FttBachc56rKgo4o2zl4pmfpqVT/
z1YqwYPUkLWNw9Hztzu/urxzJPpZx64DbpJj3ZRHH/Gj3l9IHATw2Op7ALJc881o
cY2+ViITmDCegiSavHkBH8KA/mLddalCAw5iclRkjX30Kp43jqMRCcGG+IN3UZAw
VSOrmo1JmnPGNy8DOMCCIW3gAvlXo1+ONSLNGiQPGaRPOMhI518LHnIXVB+Uixbl
Gdvx6CaaYPVWF/JJ3WnZMcUu9nvx5c+4h41xGIWUHBp2ws2357f3NMcH8wmD0WyL
jlpozEh0U+zmsaM5D4LhjVIPfMgBYI9vNufLTmo8D902Mq4tJhHJyqvwCEOkjUMI
ItyISo3KpXa96Hl74522n1k0Rph1TZ/BPved/8biFsoDP7aAFKNgB5X1acgmMNzS
XQE2zYmBNAYf5kOSQPotWDvg4OmYNxtAwcZvCrtz+O6bSpozLcRgB7lCip130Yu+
5Odl8pioZQcCtLdNwt/upP3pD+/paf6A+xf7A0Jx0tQfTr7UQA4VMxYn7gUfNA==
=QHm0
-----END PGP MESSAGE-----');*/

// Test of --detach-sig
/*if($gpg->detachSign('./encodable.txt', './encodeble.txt.sig', 'import@test')==FALSE){
	echo('Exit code: '.$gpg->exitCode);			
}else{
	print 'Success';
}*/

/*if($gpg->verify('./encodable.txt', './encodeble.txt.sig', 'import@test')==FALSE){
	echo('Exit code: '.$gpg->exitCode);			
}else{
	print 'Success';
}*/

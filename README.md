# GPGShell
Interface for gpg (GnuPG) shell commands
Inspired by https://maslosoft.com/blog/2017/09/12/using-gpg-with-php-on-server/
Documentation for ---with-colons directive: https://git.gnupg.org/cgi-bin/gitweb.cgi?p=gnupg.git;a=blob_plain;f=doc/DETAILS

## Dependency 
GnuPG (https://gnupg.org/)
## Setup
- Create folder for GnuPG's data in the GpgShell folder (linux)
```
sudo mkdir -p .gnupg
sudo chown -R www-data:www-data .gnupg
sudo chmod 700 .gnupg
```
- Generate keypair
```
sudo gpg --quick-generate-key --homedir .gnupg test@gpgshell.example
```
- Test with php built-in server
```
sudo php -S localhost:8000 // in GPGShell-master folder

Then type into your browser: http://localhost:8000/GPGTest.php 
```
## Featured GnuPG directives
- --list-keys
- --list-secret-keys
- --export
- --export-secret-key
- --import
- --encrypt
- --decrypt
- --detach-sign
- --verify
- --list-packets
- --import-ownertrust
- --gen-key
## Sample gpg commands
### Internal commands
- Delete keypairs
```
sudo gpg --homedir .gnupg --delete-secret-key test@gpgshell.example
sudo gpg --homedir .gnupg --delete-key test@gpgshell.example
```
- Edit keys
```
sudo gpg --local-user www-data --edit-key --homedir .gnupg test@gpgshell.example
```
### Batch commands
- List public and private keys
```
sudo gpg --batch --quiet --local-user www-data --list-keys --homedir .gnupg
sudo gpg --batch --quiet --local-user www-data --list-secret-keys --homedir .gnupg
```
- Export keypair
```
sudo gpg --batch --quiet --local-user www-data --armor --homedir .gnupg --export-secret-key test@gpgshell.example > privatekey.gpg
sudo gpg --batch --quiet --local-user www-data --armor --homedir .gnupg --export test@gpgshell.example > publickey.gpg
```
- Import keypair
```
sudo gpg --batch --quiet --local-user www-data --armor --homedir .gnupg --import privatekey.gpg
sudo gpg --batch --quiet --local-user www-data --armor --homedir .gnupg --import publickey.gpg
then interactive command:
sudo gpg --local-user www-data --edit-key --homedir .gnupg test@gpgshell.example
gpg> trust
```
- Decrypt
```
sudo gpg --batch --quiet --local-user www-data --decrypt --homedir .gnupg --output decrypted.txt message.gpg
```
- Encrypt
```
sudo gpg --batch --quiet --local-user www-data --armor --encrypt --homedir .gnupg --output message.gpg --recipient test@gpgshell.example encodable.txt
```
- Detach Sign
```
gpg --detach-sign --armor --batch --quiet --yes --output  ./encodeble.txt.sig --recipient import@test ./encodable.txt
```
- Verify
```
gpg --batch --quiet --recipient import@test --verify ./encodeble.txt.sig ./encodable.txt
```
- List packets
```
gpg --list-packets --batch --homedir .gnupg test.pgp
```
## GPGShell library for CodeIgniter
### Load library
```
$this->load->library('gpgshell_lib');
```
### Using gpgshell_lib library
```
$this->gpgshell_lib->encrypt(.......)
```
## Version history
### 1.1.0.2 verion
- Add importOwnertrust
- Add genKey
- Add getKeyFingerprintsByEmail
- Add getSecretKeyFingerprintsByEmail
- Add deleteSecretKeyByFingerprint
- Add deleteKeyByFingerprint
- Add deleteAllSecretKeyByEmail
- Add deleteAllKeyByEmail
### 1.2.0.3 verion
- new Programmable gpg exe name

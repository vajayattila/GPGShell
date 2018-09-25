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

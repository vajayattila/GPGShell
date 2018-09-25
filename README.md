# GPGShell
Interface for gpg (GnuPG) shell commands
## Dependency 
GnuPG (https://gnupg.org/)
## Setup
- Create folder for GnuPG's data in the GpgShell folder
```
sudo mkdir -p .gnupg
sudo chown -R www-data:www-data .gnupg
sudo chmod 700 .gnupg
```
- Generate keypair
```
sudo gpg --quick-generate-key --homedir .gnupg test@gpgshell.example
```

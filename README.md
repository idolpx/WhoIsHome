# WhoIsHome
A simple network based presence detection system.

## Requirements

nmap      - https://nmap.org/
curl      - https://curl.haxx.se/

## Windows Setup
If running on a windows web server, create "bin" folder and put nmap.exe & curl.exe there.

Setup a scheduled task to run the following command every 5mins as administrator

curl -L -u media:media --max-redirs 999 http://192.168.1.240/whoishome/cron.php


## Linux Setup

On linux create a file named "whoishome" in /etc/sudoers.d that contains the following line.
-------
www-data ALL=(ALL) NOPASSWD: /usr/bin/nmap
-------

Add Cron Job to run cron.php script as user www-data every 5mins

*/5 * * * * curl -L -u media:media --max-redirs 999 http://192.168.1.240/whoishome/cron.php

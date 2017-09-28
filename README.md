# WhoIsHome
A simple network based presence detection system.

## Requirements

composer  - https://getcomposer.org/
nmap      - https://nmap.org/<br>
curl      - https://curl.haxx.se/

## Installation

Clone "WhoIsHome" repo to a folder in your web servers document root.
Change directory into that folder and issue the following command.

```
composer install
```

```
chown -R http:http .
```

Change directory into the "includes" folder and edit the settings in "config.php".


## Windows Setup
If running on a windows web server, create "bin" folder and put nmap.exe & curl.exe there.

Setup a scheduled task to run the following command every 5mins as administrator

```
curl -L -u media:media --max-redirs 999 http://192.168.1.240/whoishome/cron.php
```

## Linux Setup

On linux create a file named "whoishome" in /etc/sudoers.d that contains the following line.

```
www-data ALL=(ALL) NOPASSWD: /usr/bin/nmap
```

Add Cron Job to run cron.php script as user www-data every 5mins

```
*/5 * * * * curl -L -u media:media --max-redirs 999 http://192.168.1.240/whoishome/cron.php
```

# WhoIsHome
A simple network based presence detection system.

## Requirements

composer  - https://getcomposer.org/<br>
nmap      - https://nmap.org/<br>
curl      - https://curl.haxx.se/

## Installation

Clone "WhoIsHome" repo to a folder in your web servers document root.
Change directory into that folder and issue the following command.

```
composer install
```

Set owner to the user tha tyour web service runs as.
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
Change "http" to the user that your web service runs as.

```
http ALL=(ALL) NOPASSWD: /usr/bin/nmap
```

Add Cron Job to run cron.php script every 5mins

```
*/5 * * * * curl -L -u media:media --max-redirs 999 http://192.168.1.240/whoishome/cron.php
```


## Usage

Goto  http://localhost/whoishome to get to the main user interface.

Use http://localhost/whoishome/cron.php?debug=1 to trigger the scan manually and see the debug output.
You can also assign a group and name to each device that is found and update, delete or wake a device on the network.

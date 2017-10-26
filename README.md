# WhoIsHome
A simple network based presence detection system.  It works by using nmap to do a ping sweep of the network every 5 minutes.  It matches the MAC address to each device found to known devices and compares the last time it was seen to current scan time to determine if the device is Home, Away, Offline, Online or Unknown.  

Mobile devices can be Home or Away.
- A Home event is triggered immediately when the device is seen on the network and the current status is Away
- A device is determined away when it has not been seen within 30min (configureable) and an Away event is triggered

(Note: Some devices go online/offline a lot to conserve battery power. That is the reason for the 30min window when determining that a device is away.)

Stationary devices can be Online or Offline.
- Currently no event is triggered when a stationary device changes status but it would be easy to add an event

Unknown devices will trigger an event until it is identified.

I have the event on my system set to send an email to the SMS gateway of my service provider to send a text message to my phone.  The event can be configured to do just about anything though.

Currently I am running this on a PogoPlug v2 E02 and Arch Linux with Apache.
I have also tested this on Windows running IIS.

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

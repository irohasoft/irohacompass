# iroha Compass
iroha Compass is a Sele-directed Learning Support System.  [[Japanese / 日本語]](/README.jp.md)

## Project website
http://irohacompass.irohasoft.jp/

## Demo
http://demoic.irohasoft.com/

## System Requirements
* PHP : 5.4 or later
* MySQL : 5.1 or later
* CakePHP : 2.10.x

## Installation
1. Download the source of iroha Compass.
https://github.com/irohasoft/irohacompass/releases
* Download the source of CakePHP.
https://github.com/cakephp/cakephp/releases/tag/2.10.13
* Make [cake] directory on your web server and upload the source of CakePHP.
* Upload the source of iroha Compass to public direcotry on your web server.  
/cake  
┗ /lib  
/public_html  
┣ /Config  
┣ /Controller  
┣ /Model  
┣ ・・・  
┣ /View  
┗ /webroot  
* Modify the database configuration on Config/database.php file.
Make sure you have created an empty database on you MySQL server.
6. Open http://(your-domain-name)/install on your web browser.

## Features

### For students.

- Set learning theme and objective.
- Show recent progress.
- Show information.
- Manage learning tasks.
- Manage learning progress.

### For teachers.
- Manage users.
- Manage user's group.
- Manage information.
- Manage student's learning theme.
- Manage student's learning tasks.
- Manage student's learning progress.

### For administrators.
- System setting

##License
GPLv3

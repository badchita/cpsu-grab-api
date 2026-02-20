# cpsu-grab-api

## Follow instructions and links to download below. Pre-requisite:
1. github account
2. github desktop
3. xampp
4. composer

## Create Github account
https://github.com/

## Install Github desktop
https://desktop.github.com/

## Install xampp
https://www.apachefriends.org/

## Install composer (click Composer-Setup.exe)
https://getcomposer.org/download/

## After finish installing, follow the steps for running the api

## Running cpsu-grab-api

(When First time running locally)
1. Start xampp
2. open http://localhost/phpmyadmin/index.php on your browser
3. create database 'cpsu-grab-api'
4. Open your command prompt
5. go to \cpsu-grab-api
6. type 'composer install'
7. open folder on text editor (vsCode, notepad ++)
8. create new file inside folder name it '.env'
9. open '.env.example' file and copy everythin and paste to '.env' file
10. open '.env' file and find 'DB_DATABASE=laravel' and replace it to 'DB_DATABASE=cpsu-grab-api' then save
11. go back to command prompt
12. type 'php artisan migrate:fresh'
13. type 'php artisan db:seed --class=AdminUserSeeder'
14. type 'php artisan serve'

(After pulling changes from repository)
Follow the these steps from (When First time running locally) <br/>
[1. => 4. => 5. => 12. => 13.]

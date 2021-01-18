- Requires
    - Php 7.1 or greater
    - Additional Php: php-wget php-xml php-json php-int php-ext php-fpm php-mysql
    - MySql 5.7 or greater
    - Composer v1.9 or greater

Installation steps:
- Copy sourcecode into destination folder. 
- Configure the webserver like NGINX with the included config file `nginx-test.conf` 
  edit the file and set root folder and domain on lines 2 and 3.
  after that give www-data permission to all root folder to allow permission.
  
- Create an empty mysql database, insert database name and user account on file:  .env
    es: DATABASE_URL=mysql://user:pass@dbhost:3306/dbname
- Run command: `composer install`
  this command will download all assets required to run it.
    
- from console run follow commands: 
    - `php bin/console make:migration`
    - `php bin/console doctrine:migrations:migrate`
    Those commands will create tables and relation between tables, according to the Entities Class `App/Entity/`
    After previous steps create a user on users table to allow login:
    - `php bin/console security:encode-password` This will generate a encrypted password.
    - Replace  {password} with response of previous command on follow command : 
    `php bin/console doctrine:query:sql 'INSERT INTO users (user_name,email,roles,password) VALUES('admin','admin@test.com','{"role": "ROLE_ADMIN"}','{password}')`
    this command will create the user admin@test.com and related password to access on web interface.
    


After all previous step are done, login at the url: http://domain.com/Login
insert user and password.
You will be redirected to a /Dashboard page where u can perform a file upload.

Any question pls feel free to contact me. 

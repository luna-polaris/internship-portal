# Industrial Internship Matching Portal (InternHub)

1. Put this folder under xampp/htdocs

2. Download https://getcomposer.org/download/ , download and run Composer-Setup.exe

3. Go to xampp/apache/conf/httpd.conf,  add "Listen 666" Under Listen 80

4. Go to xampp/apache/conf/extra/httpd-vhosts.conf add 
<VirtualHost *:666>
    ServerName internhub.local
    DocumentRoot "full/path to assignment folder/laravel/public" 

    <Directory "full/path to assigment folder/laravel/public" >
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
# Remember to change the file path

5. Turn on Apache and MySQL service in XAMPP

6. Press admin button beside MySQL in XAMPP 

7. At VScode, views > terminal > new terminal, type
cd laravel
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link


9. Go to 127.0.0.1:666


# Notes
1. Username : admin, Password:  ChangeMe123!   (refer .env)
2. Password for demo account (employer): password123


# noframe
noframe is a framework of no framework   
自由组装，自由搭配！

# rewrite
## apach
```
<VirtualHost *:80>
    DocumentRoot "E:/www/noframe/public"
    ServerName noframe.lc
    DirectoryIndex web.php
    RewriteEngine On
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
    RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
    RewriteRule . /web.php
</VirtualHost>
```

## nginx
```
server {
    listen       80;
    server_name  api.noframe.lc
    root        /var/www/noframe/public/;

    location / {
        index  api.php;
        try_files $uri $uri/ /api.php?$query_string;
    }

    location ~ \.php {
        include        fastcgi_params;
        fastcgi_pass   unix:/var/run/php-fpm.sock;
        fastcgi_index  api.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param  SCRIPT_NAME $fastcgi_script_name;
    }
}
```

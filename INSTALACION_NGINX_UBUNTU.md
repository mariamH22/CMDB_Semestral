# Instalacion en Ubuntu/Nginx

## Requisitos

- PHP-FPM 8.2 o superior.
- MySQL/MariaDB.
- Extensiones PHP: `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `openssl`, `sodium`.

## Base de datos

```bash
mysql -u laravel_user -p cmdb_integral < database/install/fresh_install.sql
```

Para actualizar una base existente, aplicar las migraciones pendientes y luego:

```bash
php database/tools/verify_environment.php
```

## Configuracion local

```bash
cp app/Config/config.local.example.php app/Config/config.local.php
```

Editar usuario, contrasena y base real.

## Nginx sugerido

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/CMDB_Semestral/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
```

## Verificacion

```bash
php database/tools/verify_environment.php
find app public tests database/tools -name '*.php' -print0 | xargs -0 -n1 php -l
for f in tests/*.php; do php "$f" || exit 1; done
```

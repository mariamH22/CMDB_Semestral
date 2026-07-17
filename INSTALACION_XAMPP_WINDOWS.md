# Instalacion en XAMPP Windows

## Requisitos

- PHP 8.2 o superior.
- MySQL/MariaDB de XAMPP.
- Extensiones PHP: `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `openssl`, `sodium`.

## Pasos

1. Copiar el proyecto en `C:\xampp\htdocs\CMDB_Semestral`.
2. Crear la base ejecutando en phpMyAdmin o consola:

```sql
SOURCE database/install/fresh_install.sql;
```

3. Crear `app/Config/config.local.php` desde `app/Config/config.local.example.php`.
4. Ajustar credenciales:

```php
'host' => 'localhost',
'database' => 'cmdb_integral',
'user' => 'root',
'password' => '',
```

5. Verificar:

```bash
php database/tools/verify_environment.php
```

6. Abrir:

```text
http://localhost/CMDB_Semestral/public/
```

## Nota

Si XAMPP usa otra contrasena, actualizar `config.local.php` antes de probar el portal.

<?php
return [
    'db' => [
        // Opcional. Si tu entorno local no usa root sin contraseña, ajusta estos valores en config.local.php.
        // En WampServer predeterminado puedes dejar user root y password vacío.
        'host' => 'localhost',
        'database' => 'cmdb_integral',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        // Plantilla sin secreto real. Copiar como config.local.php y usar una clave aleatoria fuerte fuera de Git.
        'integrity_key' => '',
        // Carpeta fuera del proyecto/repositorio. Definirla solo en config local no versionada o variable de entorno.
        'key_store_path' => '',
        // Clave para cifrar llaves privadas RSA almacenadas por el sistema. No reutilizar credenciales de MySQL.
        'key_encryption_key' => '',
        // Clave maestra externa para cifrar claves de licencia. No subirla al repositorio.
        'license_key_encryption_key' => '',
    ],
];

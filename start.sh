#!/bin/bash
# Crea directorio /data si no existe y asegura permisos
mkdir -p /data && chmod 777 /data

# Ejecuta creardb.php solo si no existe la BD
[ ! -f /data/urls.db ] && php creardb.php

# Inicia el servidor
php -S 0.0.0.0:$PORT -t .
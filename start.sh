#!/bin/bash
# Ejecuta creardb.php solo si no existe la BD
[ ! -f /data/urls.db ] && php creardb.php

# Inicia el servidor web (Railway usa PHP built-in server)
php -S 0.0.0.0:$PORT -t .
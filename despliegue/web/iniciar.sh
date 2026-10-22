#!/bin/sh
# iniciar.sh - Se ejecuta cada vez que arranca el contenedor web.
# Escribe el .env de la aplicación con los datos que pasa docker-compose
# y después inicia Apache. Así la contraseña no queda guardada en la imagen.

cat > /var/www/html/.env <<EOF
BD_HOST="$BD_HOST"
BD_PORT="$BD_PORT"
BD_USER="$BD_USER"
BD_PASS="$BD_PASS"
BD_NAME="$BD_NAME"
EOF

# Solo Apache (www-data) puede leer el archivo
chown www-data:www-data /var/www/html/.env
chmod 600 /var/www/html/.env

exec apache2-foreground

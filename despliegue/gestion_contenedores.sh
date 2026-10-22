#!/bin/bash
# gestion_contenedores.sh - Administra los contenedores del SGRSI con docker compose.
# Se ejecuta en el servidor del proyecto, desde la carpeta despliegue.
# Necesita que el usuario esté en el grupo docker (o ejecutarlo con sudo).

# Se trabaja siempre en la carpeta donde está docker-compose.yml
cd "$(dirname "$0")" || exit 1

DIR_RESPALDOS="$(pwd)/respaldos_bd"
RETENCION=7

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker no está instalado en este servidor."
    exit 1
fi

if ! docker info >/dev/null 2>&1; then
    echo "No hay permiso para usar Docker."
    echo "Agregue su usuario al grupo docker o ejecute el script con sudo."
    exit 1
fi

if [ ! -f .env ]; then
    echo "Falta el archivo .env. Créelo con: cp .env.example .env"
    exit 1
fi

pausa() {
    echo
    read -r -p "Pulse Enter para continuar..." _
}

# Pide el nombre de un servicio y solo acepta los dos del proyecto.
elegir_servicio() {
    read -r -p "Servicio (web o bd): " servicio
    [ "$servicio" = "web" ] || [ "$servicio" = "bd" ]
}

# Copia la base de datos del contenedor a un archivo comprimido.
# La contraseña de root se usa dentro del contenedor, sin escribirla acá.
respaldar_bd() {
    local fecha archivo

    mkdir -p "$DIR_RESPALDOS"
    chmod 700 "$DIR_RESPALDOS"
    fecha=$(date +%Y-%m-%d_%H-%M-%S)
    archivo="$DIR_RESPALDOS/sgrsi_bd_$fecha.sql.gz"

    if docker compose exec -T bd sh -c \
        'mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" --single-transaction --routines --databases "$MARIADB_DATABASE"' \
        | gzip > "$archivo" && [ "${PIPESTATUS[0]}" -eq 0 ]; then
        chmod 600 "$archivo"
        echo "Respaldo creado: $archivo"
        # Retención: se conservan los últimos respaldos
        ls -1t "$DIR_RESPALDOS"/sgrsi_bd_*.sql.gz | tail -n +$((RETENCION + 1)) | xargs -r rm -f
    else
        rm -f "$archivo"
        echo "No se pudo respaldar la base. ¿Está iniciado el contenedor bd?"
    fi
}

# Carga un respaldo en el contenedor. Reemplaza los datos actuales, por eso pide confirmación.
restaurar_bd() {
    local lista numero confirmar

    mapfile -t lista < <(ls -1t "$DIR_RESPALDOS"/sgrsi_bd_*.sql.gz 2>/dev/null)
    if [ "${#lista[@]}" -eq 0 ]; then
        echo "No hay respaldos en $DIR_RESPALDOS."
        return
    fi

    for numero in "${!lista[@]}"; do
        echo "  $((numero + 1)). $(basename "${lista[$numero]}")"
    done
    read -r -p "Número del respaldo: " numero
    if [[ ! "$numero" =~ ^[0-9]+$ ]] || [ "$numero" -lt 1 ] || [ "$numero" -gt "${#lista[@]}" ]; then
        echo "Número no válido."
        return
    fi

    read -r -p "Se reemplazarán los datos actuales. ¿Confirma? (s/n): " confirmar
    if [ "$confirmar" != "s" ]; then
        echo "Operación cancelada."
        return
    fi

    if gzip -dc "${lista[$((numero - 1))]}" | docker compose exec -T bd sh -c \
        'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD"'; then
        echo "Base de datos restaurada."
    else
        echo "No se pudo restaurar la base."
    fi
}

opcion=""

while [ "$opcion" != "0" ]; do
    echo
    echo "=========================================="
    echo "   CONTENEDORES DEL SGRSI"
    echo "=========================================="
    echo "1. Ver estado de los contenedores"
    echo "2. Desplegar o actualizar (construye e inicia)"
    echo "3. Detener los contenedores"
    echo "4. Reiniciar un servicio"
    echo "5. Ver los registros de un servicio"
    echo "6. Ver uso de recursos"
    echo "7. Ver volúmenes y redes del proyecto"
    echo "8. Respaldar la base de datos"
    echo "9. Restaurar la base de datos"
    echo "10. Eliminar los contenedores (conserva los datos)"
    echo "0. Salir"
    echo "=========================================="

    read -r -p "Seleccione una opcion: " opcion || opcion="0"

    case "$opcion" in
        1) docker compose ps ;;
        2) docker compose up -d --build && docker compose ps ;;
        3) docker compose stop ;;
        4) if elegir_servicio; then docker compose restart "$servicio"; else echo "Servicio no válido."; fi ;;
        5) if elegir_servicio; then docker compose logs --tail 30 "$servicio"; else echo "Servicio no válido."; fi ;;
        6) docker stats --no-stream sgrsi-web sgrsi-bd ;;
        7) echo "Volúmenes:"
           docker volume ls --filter label=com.docker.compose.project=sgrsi
           echo "Redes:"
           docker network ls --filter label=com.docker.compose.project=sgrsi ;;
        8) respaldar_bd ;;
        9) restaurar_bd ;;
        10) docker compose down ;;
        0) echo "Saliendo del programa." ;;
        *) echo "Opcion no valida." ;;
    esac

    [ "$opcion" != "0" ] && pausa
done

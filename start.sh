#!/usr/bin/env bash
set -euo pipefail

APP_URL="http://localhost:8080"
PMA_URL="http://localhost:8081"
DB_CONTAINER="estoque_mysql"
DB_NAME="estoque"
DB_ROOT_USER="root"
DB_ROOT_PASS="root"
APP_CONTAINER="estoque_php_app"

compose_cmd() {
  if docker compose version >/dev/null 2>&1; then
    docker compose "$@"
    return
  fi

  if command -v docker-compose >/dev/null 2>&1; then
    docker-compose "$@"
    return
  fi

  echo "Erro: Docker Compose nao encontrado (docker compose ou docker-compose)." >&2
  exit 1
}

wait_for_mysql() {
  echo "Aguardando MySQL ficar pronto..."
  for _ in $(seq 1 60); do
    if docker exec "${DB_CONTAINER}" \
      mysqladmin ping -h127.0.0.1 -u"${DB_ROOT_USER}" -p"${DB_ROOT_PASS}" --silent \
      >/dev/null 2>&1; then
      echo "MySQL pronto."
      return
    fi
    sleep 2
  done

  echo "Erro: MySQL nao ficou pronto a tempo." >&2
  exit 1
}

find_default_sql() {
  local candidates=(
    "database/init.sql"
    "database/schema.sql"
    "db/init.sql"
    "db/schema.sql"
    "init.sql"
    "schema.sql"
  )

  for candidate in "${candidates[@]}"; do
    if [ -f "${candidate}" ]; then
      echo "${candidate}"
      return
    fi
  done

  local any_sql
  any_sql="$(find . -maxdepth 3 -type f -name '*.sql' | head -n 1 || true)"
  if [ -n "${any_sql}" ]; then
    echo "${any_sql#./}"
  fi
}

maybe_import_sql() {
  local sql_file="${1:-}"
  local table_count

  table_count="$(docker exec "${DB_CONTAINER}" mysql -N -s \
    -u"${DB_ROOT_USER}" -p"${DB_ROOT_PASS}" -D "${DB_NAME}" \
    -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';")"

  if [ "${table_count}" != "0" ]; then
    echo "Banco ja possui tabelas (${table_count}). Importacao ignorada."
    return
  fi

  if [ -z "${sql_file}" ]; then
    sql_file="$(find_default_sql || true)"
  fi

  if [ -z "${sql_file}" ]; then
    if [ -f "back/scripts/bootstrap_acesso.php" ]; then
      echo "Banco vazio e sem .sql. Aplicando bootstrap de acesso..."
      docker exec "${APP_CONTAINER}" php /var/www/html/back/scripts/bootstrap_acesso.php
      return
    fi

    echo "Banco vazio e nenhum .sql encontrado automaticamente."
    echo "Quando tiver o dump, rode: ./start.sh caminho/arquivo.sql"
    return
  fi

  if [ ! -f "${sql_file}" ]; then
    echo "Erro: arquivo SQL nao encontrado: ${sql_file}" >&2
    exit 1
  fi

  echo "Importando SQL: ${sql_file}"
  docker exec -i "${DB_CONTAINER}" mysql \
    -u"${DB_ROOT_USER}" -p"${DB_ROOT_PASS}" "${DB_NAME}" < "${sql_file}"
  echo "Importacao concluida."
}

main() {
  local sql_file="${1:-}"

  echo "Subindo containers..."
  compose_cmd up -d --build

  wait_for_mysql
  maybe_import_sql "${sql_file}"

  echo
  echo "Sistema pronto:"
  echo "- App: ${APP_URL}"
  echo "- phpMyAdmin: ${PMA_URL}"
  echo "- DB host interno: db"
  echo "- DB nome: ${DB_NAME}"
  echo "- DB root: ${DB_ROOT_USER}/${DB_ROOT_PASS}"
}

main "${1:-}"

#!/usr/bin/env bash
set -euo pipefail

APP_CONTAINER="estoque_php_app"

if ! command -v docker >/dev/null 2>&1; then
  echo "Erro: docker nao encontrado." >&2
  exit 1
fi

if ! docker ps --format '{{.Names}}' | grep -qx "${APP_CONTAINER}"; then
  echo "Container ${APP_CONTAINER} nao esta em execucao."
  echo "Suba o ambiente primeiro com: ./start.sh"
  exit 1
fi

echo "Executando bootstrap de acesso..."
docker exec "${APP_CONTAINER}" php /var/www/html/back/scripts/bootstrap_acesso.php
echo "Pronto. Recarregue a pagina de cadastro."


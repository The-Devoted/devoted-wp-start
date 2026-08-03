#!/bin/bash
set -euo pipefail

# Load configuration from a git-ignored .env file, if present. See
# .env.template for the full list of variables this script expects.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ -f "$SCRIPT_DIR/.env" ]; then
  set -a
  source "$SCRIPT_DIR/.env"
  set +a
fi

task=${1:-}

# ----------------------------
# Required configuration
# ----------------------------
# All of these must be set (typically via .env) before any task can run.
: "${DO_DROPLET_HOST:?Set DO_DROPLET_HOST in .env (remote server host/IP)}"
: "${DO_DROPLET_USERNAME:?Set DO_DROPLET_USERNAME in .env (remote ssh user)}"
: "${DOMAIN_NAME:?Set DOMAIN_NAME in .env (remote site domain, no scheme)}"
: "${REMOTE_APP_DIR:?Set REMOTE_APP_DIR in .env (app directory on remote, relative to the ssh user home directory)}"
: "${LOCAL_URL:?Set LOCAL_URL in .env (e.g. http://localhost:8000)}"

REMOTE_SSH_TARGET="${DO_DROPLET_USERNAME}@${DO_DROPLET_HOST}"

# The deploy key lives in the repo (git-ignored) rather than ~/.ssh, so this
# script works the same for anyone who clones the repo and drops a key in.
# See README: "Pulling data from the remote server".
DEPLOY_SSH_KEY="$SCRIPT_DIR/.ssh/deploy_key"
if [ ! -f "$DEPLOY_SSH_KEY" ]; then
  echo "Missing deploy key at $DEPLOY_SSH_KEY (see README: 'Pulling data from the remote server')." >&2
  exit 1
fi
SSH_OPTS=(-i "$DEPLOY_SSH_KEY" -o IdentitiesOnly=yes)

# ----------------------------
# Database Pull
# ----------------------------
# Exports the remote database with wp-cli (reading credentials straight out
# of wp-config.php, so none live in this script or .env) and streams it
# directly into the local Docker WordPress container, keeping a timestamped
# copy along the way. Then rewrites the site URL for local dev with
# `wp search-replace`, which — unlike a raw find/replace on the SQL dump —
# correctly re-serializes PHP-serialized values instead of corrupting them.

db_pull () {
  echo "⬇️  Exporting remote DB via wp-cli and streaming into local container..."
  mkdir -p backups/db
  ssh "${SSH_OPTS[@]}" "$REMOTE_SSH_TARGET" "docker exec wordpress wp db export - --allow-root" \
    | tee "backups/db/database_backup_$(date +%Y-%m-%d_%H.%M.%S).sql" \
    | docker exec -u www-data -i wordpress wp db import -

  echo "🔀 Rewriting remote URLs for local dev..."
  docker exec -u www-data wordpress wp search-replace \
    "https://${DOMAIN_NAME}" "${LOCAL_URL}" --all-tables --report-changed-only

  echo -e "✅ Local database refreshed from remote. Huzzah! \n"
}

# ----------------------------
# Assets Pull
# ----------------------------
# Mirrors the remote wp-content/uploads directory into the local one.

assets_pull () {
  echo "🏞️  Syncing wp-content/uploads from remote development server..."
  rsync -az -e "ssh -i ${DEPLOY_SSH_KEY} -o IdentitiesOnly=yes" \
    "${REMOTE_SSH_TARGET}:${REMOTE_APP_DIR}/wp-content/uploads/" wp-content/uploads/
  echo -e "✅ Local assets refreshed from remote. Awesome! \n"
}

# ----------------------------
# Development Prep
# ----------------------------
# Convenience command that pulls both the database and assets, in the order
# needed for local development.

dev_prep () {
  echo -e "\n~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
  echo "| Prepare Local Development Environment |"
  echo -e "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n"

  db_pull
  assets_pull

  echo -e "🎉 Your local development environment is now prepared. Happy coding! \n"
}

# Perform the task selected by the user.
case "$task" in
  db_pull) db_pull ;;
  assets_pull) assets_pull ;;
  dev) dev_prep ;;
  *)
    echo "You must specify a task. Example: $ bash db_utils.sh dev"
    echo "Options are: db_pull | assets_pull | dev"
    ;;
esac

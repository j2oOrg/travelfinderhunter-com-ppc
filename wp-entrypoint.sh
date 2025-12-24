#!/bin/bash
set -e

# Default WordPress database settings inside the container.
WP_DB_NAME=${WORDPRESS_DB_NAME:-wordpress}
WP_DB_USER=${WORDPRESS_DB_USER:-wpuser}
WP_DB_PASSWORD=${WORDPRESS_DB_PASSWORD:-wppassword}
WP_DB_HOST=127.0.0.1

# Optional site setup config (overridable via env vars).
CONFIG_FILE=${WP_SETUP_CONFIG:-/usr/src/wordpress/wp-setup.conf}
if [ -f "$CONFIG_FILE" ]; then
    # shellcheck disable=SC1090
    . "$CONFIG_FILE"
fi
SITE_TITLE=${WP_SITE_TITLE:-${SITE_TITLE:-travelfinderhunter}}
ADMIN_USER=${WP_ADMIN_USER:-${ADMIN_USER:-admin}}
ADMIN_PASSWORD=${WP_ADMIN_PASSWORD:-${ADMIN_PASSWORD:-changeme}}
ADMIN_EMAIL=${WP_ADMIN_EMAIL:-${ADMIN_EMAIL:-admin@example.com}}
SITE_URL=${WP_SITE_URL:-${SITE_URL:-https://travelfinderhunter.com}}
PLUGIN_DROP_DIR=${WP_PLUGIN_DROP_DIR:-/usr/src/wordpress/wp-plugins}

# Initialize MariaDB data directory on first run.
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initializing MariaDB data directory..."
    mysqld --initialize-insecure --user=mysql
fi

echo "Starting MariaDB..."
mysqld_safe --bind-address=127.0.0.1 --skip-networking=0 &

echo "Waiting for MariaDB to be ready..."
for i in $(seq 1 30); do
    if mysqladmin ping --silent; then
        break
    fi
    sleep 1
done

if ! mysqladmin ping --silent; then
    echo "MariaDB did not start properly"
    exit 1
fi

echo "Creating WordPress database and user if needed..."
mysql -uroot <<EOF
CREATE DATABASE IF NOT EXISTS \`${WP_DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${WP_DB_USER}'@'%' IDENTIFIED BY '${WP_DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${WP_DB_NAME}\`.* TO '${WP_DB_USER}'@'%';
FLUSH PRIVILEGES;
EOF

export WORDPRESS_DB_HOST=${WP_DB_HOST}
export WORDPRESS_DB_NAME=${WP_DB_NAME}
export WORDPRESS_DB_USER=${WP_DB_USER}
export WORDPRESS_DB_PASSWORD=${WP_DB_PASSWORD}

# Ensure WordPress core is present (volume-safe copy).
if [ ! -e "/var/www/html/wp-settings.php" ]; then
    echo "Copying WordPress core to /var/www/html..."
    cp -a /usr/src/wordpress/. /var/www/html/
fi

chown -R www-data:www-data /var/www/html

# Auto-generate wp-config.php if missing.
if [ ! -f "/var/www/html/wp-config.php" ]; then
    echo "Generating wp-config.php..."
    wp --path=/var/www/html --allow-root config create \
        --dbname="${WP_DB_NAME}" \
        --dbuser="${WP_DB_USER}" \
        --dbpass="${WP_DB_PASSWORD}" \
        --dbhost="${WP_DB_HOST}" \
        --skip-check
fi
# Ensure SSL is enforced in admin to match SITE_URL and avoid redirect loops behind proxies.
wp --path=/var/www/html --allow-root config set FORCE_SSL_ADMIN true --raw --type=constant

# Auto-install WordPress (idempotent).
if ! wp --path=/var/www/html --allow-root core is-installed; then
    echo "Running initial WordPress install..."
    wp --path=/var/www/html --allow-root core install \
        --url="${SITE_URL}" \
        --title="${SITE_TITLE}" \
        --admin_user="${ADMIN_USER}" \
        --admin_password="${ADMIN_PASSWORD}" \
        --admin_email="${ADMIN_EMAIL}" \
        --skip-email
fi

# Activate the bundled theme by default (idempotent).
THEME_SLUG=${WP_THEME_SLUG:-raffle}
if wp --path=/var/www/html --allow-root theme is-installed "${THEME_SLUG}"; then
    wp --path=/var/www/html --allow-root theme activate "${THEME_SLUG}" || true
    # Belt-and-suspenders: set template/style options to the active slug to avoid fallback to TwentyTwenty-*.
    wp --path=/var/www/html --allow-root option set template "${THEME_SLUG}" || true
    wp --path=/var/www/html --allow-root option set stylesheet "${THEME_SLUG}" || true
fi

# Ensure key pages exist and have the correct templates.
ensure_page() {
  local slug="$1"
  local title="$2"
  local template="$3"
  local id
  id=$(wp --path=/var/www/html --allow-root post list --post_type=page --name="$slug" --format=ids)
  if [ -z "$id" ]; then
    id=$(wp --path=/var/www/html --allow-root post create --post_type=page --post_status=publish --post_title="$title" --post_name="$slug" --porcelain)
  fi
  if [ -n "$template" ]; then
    wp --path=/var/www/html --allow-root post meta update "$id" _wp_page_template "$template" || true
  fi
}

ensure_page "rules" "Rules" "page-rules.php"
ensure_page "faq" "FAQ" "page-faq.php"
ensure_page "privacy-policy" "Privacy Policy" "page-privacy-policy.php"

# Ensure site/home URLs are aligned with SITE_URL to avoid mixed-content issues.
if [ -n "${SITE_URL}" ]; then
    wp --path=/var/www/html --allow-root option update siteurl "${SITE_URL}" || true
    wp --path=/var/www/html --allow-root option update home "${SITE_URL}" || true
fi

# Install/activate plugins dropped into ${PLUGIN_DROP_DIR} (zip files or folders).
if [ -d "${PLUGIN_DROP_DIR}" ]; then
    echo "Processing plugins in ${PLUGIN_DROP_DIR}..."
    shopt -s nullglob
    for zip in "${PLUGIN_DROP_DIR}"/*.zip; do
        echo "Installing plugin from zip: ${zip}"
        wp --path=/var/www/html --allow-root plugin install "${zip}" --force --activate || \
          echo "Warning: failed to install ${zip}" >&2
    done
    for dir in "${PLUGIN_DROP_DIR}"/*/; do
        slug=$(basename "${dir%/}")
        dest="/var/www/html/wp-content/plugins/${slug}"
        if [ ! -d "${dest}" ]; then
            echo "Copying plugin folder ${slug}..."
            cp -a "${dir}" "${dest}" || echo "Warning: failed to copy ${slug}" >&2
        fi
        wp --path=/var/www/html --allow-root plugin activate "${slug}" || \
          echo "Warning: failed to activate ${slug}" >&2
    done
    shopt -u nullglob
fi


# Install and activate FileBird plugin (idempotent, overridable).
if [ "${WP_INSTALL_FILEBIRD:-1}" = "0" ]; then
    echo "Ensuring FileBird plugin is installed..."
    if ! wp --path=/var/www/html --allow-root plugin is-installed filebird; then
        if ! wp --path=/var/www/html --allow-root plugin install filebird --activate; then
            echo "Warning: FileBird plugin installation failed; continuing without it." >&2
        fi
    else
        wp --path=/var/www/html --allow-root plugin activate filebird || \
            echo "Warning: could not activate FileBird plugin." >&2
    fi
fi



# Hand off to the original WordPress entrypoint (starts Apache/PHP).
exec docker-entrypoint.sh "$@"

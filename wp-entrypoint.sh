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
ADMIN_EMAIL=${WP_ADMIN_EMAIL:-${ADMIN_EMAIL:-support@travelfinderhunter.com}}
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

# Ensure WordPress update directory exists and is writable.
mkdir -p /var/www/html/wp-content/upgrade
chown -R www-data:www-data /var/www/html/wp-content/upgrade || true
chmod -R 775 /var/www/html/wp-content/upgrade || true

# Ensure media uploads directory exists and is writable for the web server.
mkdir -p /var/www/html/wp-content/uploads
chown -R www-data:www-data /var/www/html/wp-content/uploads || true
chmod -R 775 /var/www/html/wp-content/uploads || true

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

# Configure RapidAPI credentials for hotel search if provided.
if [ -n "${WP_RAPIDAPI_KEY:-}" ]; then
    wp --path=/var/www/html --allow-root config set TRAVEL_RAPIDAPI_KEY "${WP_RAPIDAPI_KEY}" --type=constant || true
fi
if [ -n "${WP_RAPIDAPI_HOST:-}" ]; then
    wp --path=/var/www/html --allow-root config set TRAVEL_RAPIDAPI_HOST "${WP_RAPIDAPI_HOST}" --type=constant || true
fi
if [ -n "${WP_RAPIDAPI_LOCALE:-}" ]; then
    wp --path=/var/www/html --allow-root config set TRAVEL_RAPIDAPI_LOCALE "${WP_RAPIDAPI_LOCALE}" --type=constant || true
fi
if [ -n "${WP_RAPIDAPI_DOMAIN:-}" ]; then
    wp --path=/var/www/html --allow-root config set TRAVEL_RAPIDAPI_DOMAIN "${WP_RAPIDAPI_DOMAIN}" --type=constant || true
fi

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
THEME_SLUG=${WP_THEME_SLUG:-travel}
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

ensure_page_with_content() {
  local slug="$1"
  local title="$2"
  local template="$3"
  local content="$4"
  local id
  local existing_content
  id=$(wp --path=/var/www/html --allow-root post list --post_type=page --name="$slug" --format=ids)
  if [ -z "$id" ]; then
    id=$(wp --path=/var/www/html --allow-root post create --post_type=page --post_status=publish --post_title="$title" --post_name="$slug" --post_content="$content" --porcelain)
  else
    existing_content=$(wp --path=/var/www/html --allow-root post get "$id" --field=post_content || true)
    if [ -z "${existing_content//[[:space:]]/}" ]; then
      wp --path=/var/www/html --allow-root post update "$id" --post_content="$content" || true
    fi
  fi
  wp --path=/var/www/html --allow-root post update "$id" --post_status=publish || true
  if [ -n "$template" ]; then
    wp --path=/var/www/html --allow-root post meta update "$id" _wp_page_template "$template" || true
  fi
}

ensure_category() {
  local slug="$1"
  local name="$2"
  local id
  id=$(wp --path=/var/www/html --allow-root term list category --slug="$slug" --format=ids)
  if [ -z "$id" ]; then
    id=$(wp --path=/var/www/html --allow-root term create category "$name" --slug="$slug" --porcelain)
  fi
  echo "$id"
}

ensure_post() {
  local slug="$1"
  local title="$2"
  local excerpt="$3"
  local content="$4"
  local category_id="$5"
  local id
  id=$(wp --path=/var/www/html --allow-root post list --post_type=post --name="$slug" --format=ids)
  if [ -z "$id" ]; then
    wp --path=/var/www/html --allow-root post create \
      --post_type=post \
      --post_status=publish \
      --post_title="$title" \
      --post_name="$slug" \
      --post_excerpt="$excerpt" \
      --post_category="$category_id" \
      --post_content="$content" \
      --porcelain
  fi
}

privacy_policy_content=$(cat <<'EOF'
<p>Effective date: 2025-01-01</p>
<p>Travel Finder Hunter ("we", "us") respects your privacy. This policy explains what we collect and how we use it.</p>
<h2>Information we collect</h2>
<ul>
  <li>Contact details you submit, such as email.</li>
  <li>Search queries and preferences like dates, destinations, and filters.</li>
  <li>Technical data such as IP address, device, and browser.</li>
</ul>
<h2>How we use information</h2>
<ul>
  <li>Provide search results and hotel deals.</li>
  <li>Improve site performance and personalization.</li>
  <li>Send updates if you opt in.</li>
</ul>
<h2>Cookies</h2>
<p>We use cookies and similar technologies to remember settings and measure traffic.</p>
<h2>Sharing</h2>
<p>We may share data with service providers that help us operate the site. We do not sell personal data.</p>
<h2>Data retention</h2>
<p>We keep data only as long as needed for the purposes above.</p>
<h2>Your choices</h2>
<p>You can request access or deletion of your data by emailing support@travelfinderhunter.com.</p>
<h2>Third-party links</h2>
<p>Our site links to third-party booking sites. Their policies apply when you leave our site.</p>
<h2>Updates</h2>
<p>We may update this policy. The latest version will be posted here.</p>
<p>Contact: support@travelfinderhunter.com</p>
EOF
)

terms_content=$(cat <<'EOF'
<p>Effective date: 2025-01-01</p>
<p>These terms govern your use of Travel Finder Hunter. By using this site you agree to them.</p>
<h2>Use of the site</h2>
<ul>
  <li>Use the site for lawful purposes only.</li>
  <li>Do not attempt to disrupt or misuse the service.</li>
</ul>
<h2>Hotel data and pricing</h2>
<p>Prices and availability are provided by partners and may change without notice.</p>
<h2>Bookings and third-party sites</h2>
<p>Bookings are completed on third-party sites. We are not responsible for their services or policies.</p>
<h2>Content</h2>
<p>All content is provided as-is and may change at any time.</p>
<h2>Limitation of liability</h2>
<p>To the fullest extent allowed by law, we are not liable for indirect or consequential damages.</p>
<h2>Changes</h2>
<p>We may update these terms. Continued use means acceptance of the updated terms.</p>
<h2>Contact</h2>
<p>Questions can be sent to support@travelfinderhunter.com.</p>
EOF
)

ensure_page_with_content "privacy-policy" "Privacy Policy" "" "$privacy_policy_content"
ensure_page_with_content "terms-and-conditions" "Terms and Conditions" "" "$terms_content"

ensure_page "rules" "Rules" "page-rules.php"
ensure_page "faq" "FAQ" "page-faq.php"

guide_category_id=$(ensure_category "travel-guides" "Travel Guides")

guide_lisbon=$(cat <<'EOF'
<p>Lisbon is compact, walkable, and full of distinct neighborhoods. These three areas give you a great mix of views, food, and easy transit.</p>
<h2>Stay base: Baixa or Chiado</h2>
<p>Choose Baixa for flat streets and fast access to trams, trains, and the riverfront. Chiado adds classic cafes and boutique shopping.</p>
<h2>Atmosphere: Alfama</h2>
<p>Alfama is all alleys and tiled facades. Go early for viewpoints, then linger for fado after dark.</p>
<h2>Local favorite: Principe Real</h2>
<p>Principe Real is leafy and relaxed, with small galleries and brunch spots. It is a smart pick for longer stays.</p>
<h3>Quick tips</h3>
<ul>
  <li>Use the metro and trams for hills, but budget time for climbs.</li>
  <li>Book a hotel with a terrace for sunset views.</li>
  <li>Try one day trip to Sintra and return for dinner by the river.</li>
</ul>
EOF
)

guide_kyoto=$(cat <<'EOF'
<p>Kyoto rewards slow mornings and early starts. This three day plan balances iconic temples with small streets and tea houses.</p>
<h2>Day 1: Classic east</h2>
<p>Start at Kiyomizu dera, then walk the old streets of Ninenzaka and Sannenzaka. End with sunset in Gion.</p>
<h2>Day 2: Arashiyama and river paths</h2>
<p>Visit the bamboo grove before the crowds. Later, follow the Katsura river and browse local craft shops.</p>
<h2>Day 3: Tea and gardens</h2>
<p>Head to Uji for matcha and calm riverfront walks, then return for a quiet garden visit in the afternoon.</p>
<h3>Where to stay</h3>
<ul>
  <li>Downtown for easy transit and late dining.</li>
  <li>Higashiyama for atmosphere and early temple access.</li>
  <li>Look for hotels with onsen or a private bath.</li>
</ul>
EOF
)

guide_cape_town=$(cat <<'EOF'
<p>Cape Town is a weekend city with big energy. Focus on the coast, markets, and a single mountain hike for the best balance.</p>
<h2>Friday night: Waterfront and food halls</h2>
<p>Start at the V and A Waterfront for sunset, then head to the Time Out Market for a quick taste tour.</p>
<h2>Saturday: Table Mountain and Kloof Street</h2>
<p>Take the cable car early, then spend the afternoon in Kloof Street cafes and design shops.</p>
<h2>Sunday: Beaches and markets</h2>
<p>Walk the Sea Point promenade, then drive to Camps Bay or Clifton for the afternoon.</p>
<h3>Hotel tips</h3>
<ul>
  <li>Sea Point gives you ocean views and easy rides.</li>
  <li>Gardens is central and close to parks.</li>
  <li>Pick a place with backup power for load shedding.</li>
</ul>
EOF
)

ensure_post \
  "lisbon-neighborhoods-alfama-baixa-principe-real" \
  "Lisbon Neighborhoods: Alfama, Baixa, and Principe Real" \
  "A quick guide to Lisbon neighborhoods with views, food, and easy transit." \
  "$guide_lisbon" \
  "$guide_category_id"

ensure_post \
  "kyoto-three-days-temples-tea" \
  "Kyoto in Three Days: Temples, Tea, and Quiet Streets" \
  "A three day plan that blends temples, tea stops, and calm streets." \
  "$guide_kyoto" \
  "$guide_category_id"

ensure_post \
  "cape-town-weekend-planner" \
  "Cape Town Weekend Planner: Markets, Coastlines, and Table Mountain" \
  "A focused weekend itinerary with markets, coastal walks, and a mountain view." \
  "$guide_cape_town" \
  "$guide_category_id"

beach_category_id=$(ensure_category "beach-escapes" "Beach Escapes")
world_category_id=$(ensure_category "world-highlights" "World Highlights")

guide_maldives=$(cat <<'EOF'
<p>For a pure beach reset, the Maldives is about slow mornings and clear water. Choose a calm lagoon island, then plan the rest around the tide.</p>
<h2>Best time to go</h2>
<p>Late November through April is dry and sunny. Shoulder season offers better deals with fewer crowds.</p>
<h2>Where to stay</h2>
<p>Look for resorts with a house reef and a long sandy stretch. Water villas are iconic, but beach villas are quieter.</p>
<h3>Plan it well</h3>
<ul>
  <li>Pack reef safe sunscreen and swim shoes.</li>
  <li>Book transfers with your hotel to avoid delays.</li>
  <li>Ask about meal plans if you want predictable costs.</li>
</ul>
EOF
)

guide_bali=$(cat <<'EOF'
<p>Bali balances easy beach access with food, temples, and day trips. This is a simple week that starts in Seminyak and ends in Uluwatu.</p>
<h2>Seminyak base</h2>
<p>Stay near the beach for sunset walks and quick cafes. Use a driver for temple trips or rice terraces.</p>
<h2>Uluwatu finish</h2>
<p>Head south for cliff views, surf beaches, and slower evenings.</p>
<h3>Quick tips</h3>
<ul>
  <li>Choose a hotel with a pool for mid day breaks.</li>
  <li>Plan two beach days and one cultural day.</li>
  <li>Book scooters only if you are confident on narrow roads.</li>
</ul>
EOF
)

guide_caribbean=$(cat <<'EOF'
<p>Not sure where to start in the Caribbean? These three islands cover calm waters, lively towns, and easy flights.</p>
<h2>Turks and Caicos</h2>
<p>Grace Bay has powder sand and clear water, ideal for a first trip.</p>
<h2>Aruba</h2>
<p>Consistent sunshine and long beaches make it an easy pick for winter travel.</p>
<h2>Barbados</h2>
<p>West coast for calm water, south coast for a bit more energy.</p>
<h3>Booking tips</h3>
<ul>
  <li>Compare resorts with beachfront access and shade.</li>
  <li>Book mid week to save on hotel rates.</li>
  <li>Look for packages that include airport transfers.</li>
</ul>
EOF
)

guide_istanbul=$(cat <<'EOF'
<p>Istanbul is a fast but rewarding city. Two days is enough to see the highlights if you keep the plan tight.</p>
<h2>Day one: Old City</h2>
<p>Start with Hagia Sophia, then walk to the Blue Mosque and Grand Bazaar.</p>
<h2>Day two: Bosphorus and modern streets</h2>
<p>Take a ferry for skyline views, then explore Karakoy and Galata.</p>
<h3>Stay smart</h3>
<ul>
  <li>Sultanahmet for landmarks, Karakoy for cafes.</li>
  <li>Use trams to save time between sights.</li>
  <li>Pack layers for breezy evenings.</li>
</ul>
EOF
)

guide_mexico_city=$(cat <<'EOF'
<p>Mexico City is a world class food and design destination with great walkability in key neighborhoods.</p>
<h2>Friday: Roma and Condesa</h2>
<p>Start with coffee, parks, and galleries. Save dinner for a longer tasting menu.</p>
<h2>Saturday: Centro and markets</h2>
<p>Visit the historic center, then stop at a market for tacos and fresh juice.</p>
<h2>Sunday: Museums and calm corners</h2>
<p>Pick one museum, then wrap with a neighborhood lunch.</p>
<h3>Where to stay</h3>
<ul>
  <li>Roma for dining and boutique hotels.</li>
  <li>Polanco for upscale stays and museums.</li>
  <li>Condesa for leafy streets and cafes.</li>
</ul>
EOF
)

guide_sydney=$(cat <<'EOF'
<p>Sydney blends a big city feel with quick access to the beach. This plan keeps the pace relaxed.</p>
<h2>Harbor morning</h2>
<p>Walk the Opera House area, then hop on a ferry for skyline photos.</p>
<h2>Beach afternoon</h2>
<p>Head to Bondi or Manly for a swim and a coastal walk.</p>
<h2>Local food night</h2>
<p>Explore Surry Hills for dinner and late coffee.</p>
<h3>Stay tips</h3>
<ul>
  <li>CBD for transit, Manly for beach vibes.</li>
  <li>Book early for waterfront rooms.</li>
  <li>Bring a light jacket for evening breezes.</li>
</ul>
EOF
)

ensure_post \
  "maldives-calm-lagoons-guide" \
  "Beach Escapes: Maldives for Calm Lagoons" \
  "Lagoon stays, sandbar mornings, and clear water tips for a beach reset." \
  "$guide_maldives" \
  "$beach_category_id"

ensure_post \
  "bali-beach-week-seminyak-uluwatu" \
  "Bali Beach Week: Seminyak to Uluwatu" \
  "A weeklong beach plan with easy stays, surf views, and temple day trips." \
  "$guide_bali" \
  "$beach_category_id"

ensure_post \
  "caribbean-beach-picks" \
  "Caribbean Beach Picks: Turks, Aruba, and Barbados" \
  "Three beach favorites with clear water, easy flights, and relaxed resorts." \
  "$guide_caribbean" \
  "$beach_category_id"

ensure_post \
  "istanbul-48-hours-guide" \
  "Istanbul in 48 Hours: Old City to Bosphorus" \
  "A tight two day itinerary that hits history, ferries, and modern streets." \
  "$guide_istanbul" \
  "$world_category_id"

ensure_post \
  "mexico-city-food-design" \
  "Mexico City Food and Design Weekend" \
  "A weekend plan with markets, museums, and great neighborhood stays." \
  "$guide_mexico_city" \
  "$world_category_id"

ensure_post \
  "sydney-harbor-days" \
  "Sydney Harbor Days: Beaches, Ferries, and Walks" \
  "A relaxed city break with ferry views and a beach afternoon." \
  "$guide_sydney" \
  "$world_category_id"

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

# Final permissions pass after wp-cli actions (helps with bind mounts).
chown -R www-data:www-data /var/www/html/wp-content/uploads /var/www/html/wp-content/upgrade || true
chmod -R 775 /var/www/html/wp-content/uploads /var/www/html/wp-content/upgrade || true


# Hand off to the original WordPress entrypoint (starts Apache/PHP).
exec docker-entrypoint.sh "$@"

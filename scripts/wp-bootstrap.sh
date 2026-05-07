#!/usr/bin/env bash
set -euo pipefail

# Usage:
# DB_NAME=itrs_new DB_USER=root DB_PASS=secret DB_HOST=127.0.0.1 DB_PREFIX=wp_ \
# SITE_URL=http://itrs-new.local SITE_TITLE="ITRS Nepal" ADMIN_USER=admin ADMIN_PASS=admin123 ADMIN_EMAIL=you@example.com \
# bash scripts/wp-bootstrap.sh

PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PROJECT_ROOT"

: "${DB_NAME:?DB_NAME is required}"
: "${DB_USER:?DB_USER is required}"
: "${DB_PASS:?DB_PASS is required}"
: "${DB_HOST:=127.0.0.1}"
: "${DB_PREFIX:=wp_}"
: "${SITE_URL:?SITE_URL is required}"
: "${SITE_TITLE:=ITRS Nepal}"
: "${ADMIN_USER:=admin}"
: "${ADMIN_PASS:?ADMIN_PASS is required}"
: "${ADMIN_EMAIL:?ADMIN_EMAIL is required}"

if [[ ! -f wp-config.php ]]; then
  wp config create \
    --dbname="$DB_NAME" \
    --dbuser="$DB_USER" \
    --dbpass="$DB_PASS" \
    --dbhost="$DB_HOST" \
    --dbprefix="$DB_PREFIX" \
    --skip-check
fi

if ! wp core is-installed >/dev/null 2>&1; then
  wp core install \
    --url="$SITE_URL" \
    --title="$SITE_TITLE" \
    --admin_user="$ADMIN_USER" \
    --admin_password="$ADMIN_PASS" \
    --admin_email="$ADMIN_EMAIL"
fi

wp theme activate itrs-ai

# Create required pages if they do not exist.
create_page() {
  local title="$1"
  local slug="$2"

  if ! wp post list --post_type=page --name="$slug" --field=ID --format=ids | grep -q .; then
    wp post create --post_type=page --post_title="$title" --post_name="$slug" --post_status=publish >/dev/null
  fi
}

create_page "Home" "home"
create_page "About Us" "about-us"
create_page "Services" "services"
create_page "Process" "process"
create_page "Project Planner" "project-planner"
create_page "Contact Us" "contact-us"
create_page "Blog" "blog"

HOME_ID="$(wp post list --post_type=page --name=home --field=ID --format=ids | head -n1)"
BLOG_ID="$(wp post list --post_type=page --name=blog --field=ID --format=ids | head -n1)"

wp option update show_on_front page
wp option update page_on_front "$HOME_ID"
wp option update page_for_posts "$BLOG_ID"

# Ensure primary menu exists and includes top-level links.
if ! wp menu list --fields=name --format=csv | grep -q '^Primary$'; then
  wp menu create Primary >/dev/null
fi

wp menu location assign Primary primary

add_menu_item_if_missing() {
  local menu="$1"
  local title="$2"
  local slug="$3"
  local page_id

  page_id="$(wp post list --post_type=page --name="$slug" --field=ID --format=ids | head -n1)"

  if [[ -n "$page_id" ]]; then
    if ! wp menu item list "$menu" --fields=title --format=csv | grep -Fq "$title"; then
      wp menu item add-post "$menu" "$page_id" >/dev/null
    fi
  fi
}

add_menu_item_if_missing Primary Home home
add_menu_item_if_missing Primary "About Us" about-us
add_menu_item_if_missing Primary Services services
add_menu_item_if_missing Primary Process process
add_menu_item_if_missing Primary "Project Planner" project-planner
add_menu_item_if_missing Primary "Contact Us" contact-us
add_menu_item_if_missing Primary Blog blog

if ! wp menu list --fields=name --format=csv | grep -q '^Footer$'; then
  wp menu create Footer >/dev/null
fi

wp menu location assign Footer footer

echo "Bootstrap complete. Login at ${SITE_URL}/wp-admin"

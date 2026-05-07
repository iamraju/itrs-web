# ITRS Nepal WordPress Rebuild

This workspace now contains:

- Latest WordPress core files (downloaded via WP-CLI)
- A custom Tailwind CSS theme at `wp-content/themes/itrs-ai`
- A bootstrap script to finish local setup with your DB credentials
- A root-level command to run WordPress locally: `npm run serve:wp`

## Theme Highlights

- Professional, AI-forward visual style
- Brand color foundation using `#f38250`
- Responsive layout for desktop and mobile
- Menu structure matching your required top-level items:
  - Home
  - About Us
  - Services
  - Process
  - Project Planner
  - Contact Us
  - Blog
- Blog index and single post templates
- Tailwind v4 build pipeline

## Finish Local Installation

1. Build theme assets (already built once):

   ```bash
   cd wp-content/themes/itrs-ai
   npm install
   npm run build
   ```

2. Run WordPress bootstrap using your local DB/site values:

   ```bash
   DB_NAME=itrs_new \
   DB_USER=your_db_user \
   DB_PASS=your_db_password \
   DB_HOST=127.0.0.1 \
   DB_PREFIX=wp_ \
   SITE_URL=http://itrs-new.local \
   SITE_TITLE="ITRS Nepal" \
   ADMIN_USER=admin \
   ADMIN_PASS=change-me \
   ADMIN_EMAIL=you@example.com \
   bash scripts/wp-bootstrap.sh
   ```

3. Login to `/wp-admin` and update content.

## Important Content Notes

- Logo integration is automatic when `wp-content/themes/itrs-ai/logo.png` exists.
- Project Planner and Contact Us forms are built in and submit to admin as `Leads`.
- Blog page is ready for post publishing.

## Run Locally (No vhost Required)

```bash
npm run serve:wp
```

- Site URL: `http://127.0.0.1:8090`
- Admin URL: `http://127.0.0.1:8090/wp-admin`

## Theme Development

- Watch Tailwind changes:

  ```bash
  cd wp-content/themes/itrs-ai
  npm run watch
  ```

- Main editable style source:
  - `wp-content/themes/itrs-ai/src/input.css`
- Compiled output:
  - `wp-content/themes/itrs-ai/assets/css/main.css`

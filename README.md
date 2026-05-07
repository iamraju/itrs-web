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

## Auto Deployment

This repo now includes a GitHub Actions workflow at `.github/workflows/deploy.yml`.

- Trigger: push to `develop`
- Manual trigger: GitHub Actions -> `Deploy WordPress Site` -> `Run workflow`
- Deployment method: SSH + `rsync`
- Theme build: runs automatically before upload

### What Gets Deployed

- WordPress core files tracked in the repo
- Custom theme files in `wp-content/themes/itrs-ai`
- Built Tailwind assets

### What Is Not Overwritten

- `wp-config.php`
- `wp-content/uploads/`
- local / CI `node_modules`

### Required GitHub Secrets

Set these in GitHub: `Settings -> Secrets and variables -> Actions`

- `DEPLOY_HOST`: server hostname or IP
- `DEPLOY_PORT`: SSH port, usually `22`
- `DEPLOY_USER`: SSH user for deployment
- `DEPLOY_PATH`: absolute path to the live WordPress directory on server
- `DEPLOY_SSH_PRIVATE_KEY`: private SSH key content for the deploy user

### Server Requirements

- SSH access enabled
- `rsync` installed on the server
- target directory already contains a working production `wp-config.php`
- web server user must have access to the deployed files

### Recommended First-Time Setup

1. Create a dedicated deploy SSH key pair.
2. Add the public key to the server user's `~/.ssh/authorized_keys`.
3. Add the private key to the `DEPLOY_SSH_PRIVATE_KEY` GitHub secret.
4. Set the other GitHub secrets listed above.
5. Push to `develop` or run the workflow manually.

### Important Note

Because `wp-config.php` is excluded from deployment, production SMTP, database credentials, and other environment-specific values should be configured directly on the server.

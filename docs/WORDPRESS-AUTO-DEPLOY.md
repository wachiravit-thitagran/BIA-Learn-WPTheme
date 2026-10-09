# WordPress production auto-deploy

Workflow: [`.github/workflows/deploy.yml`](../.github/workflows/deploy.yml)

## One-time configuration

Add two GitHub Actions repository secrets under **Settings > Secrets and variables > Actions**:

- `BIA_WP_USERNAME`: WordPress account name authorized to install themes.
- `BIA_WP_APP_PASSWORD`: WordPress Application Password created for that account, with permission to install/update themes.

The workflow uses HTTPS + WordPress REST Abilities endpoints. The WordPress site must expose `wordpress/theme-get` and `wordpress/theme-install-package` via REST (`show_in_rest: true`), and the authenticated account must pass their permission callbacks. ChatGPT connector availability **does not** prove these abilities are REST-exposed.

Target: `https://bia-learn.psu.ac.th/wp-json/wp-abilities/v1`

WordPress Application Passwords are created from **Users > Profile > Application Passwords** (if enabled). Store the generated value only in GitHub Secrets.

## How it runs

1. Push changed code to `main`.
2. `WP Integration` checks the commit.
3. `Release theme` builds and validates `bia-learn.zip`, then publishes a versioned Release.
4. Release `published` triggers `Deploy theme to WordPress`.
5. Workflow checks secrets, ensures this is the **latest** stable Release and its ZIP exists, confirms REST permissions with read-only `theme-get`, installs via `theme-install-package`, then verifies version and active status with `theme-get`.

It deliberately **refuses to deploy older releases**. Deployment is serialized, and a failed check stops the job.

## After adding secrets

Go to **Actions > Deploy theme to WordPress > Run workflow**. Enter the **current latest** Release tag, for example `v1.0.96`, for the first smoke test. Note: this manual action really installs/overwrites the theme; it is not dry-run.

If authentication succeeds but `theme-get` returns 404, REST exposure for that ability is missing. If it returns 403, check permission callback and WordPress account permissions. If the POST returns a WordPress error, inspect the workflow output and verify server filesystem permissions.

**Important**: an unsuccessful deployment will not roll back automatically. Take a WordPress backup before enabling unattended production deployments. This only checks installation/version/active state, not the visual behavior in a browser.

Workflow source lives in the repository; GitHub Action secrets are managed by the site administrator and are not committed.

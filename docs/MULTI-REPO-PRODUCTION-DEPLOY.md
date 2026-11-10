# BIA E-Learning — multi-repository production deployment runbook

Last verified: 2026-11-10 (Asia/Bangkok). Production: https://bia-learn.psu.ac.th

This document records the **working ChatGPT WordPress MCP deployment method**, separately from the theme's optional [GitHub Actions auto-deploy](WORDPRESS-AUTO-DEPLOY.md). A successful GitHub Release is **not** evidence that the release was installed in WordPress.

## Source of truth and scope

- GitHub owner: `wachiravit-thitagran`; use the latest **published, non-draft, non-prerelease** GitHub Release assets, not a repository source-code ZIP.
- Production connection: BIA E-Learning WordPress MCP Adapter via the registered WordPress Abilities API.
- Confirm the active plugin/theme and installed versions **before** making changes, and again **after** all installs.
- Do not update unrelated WordPress.org plugins, enable new plugins, change credentials, or alter production data as part of this runbook.
- Before deploying, ensure there is a usable backup/rollback plan; check release CI and compatibility. Production installation may overwrite working code and does not automatically roll back.

## Repository → release ZIP → installed WordPress path

| GitHub repository | Expected Release ZIP asset | Installed plugin folder/file or theme |
| --- | --- | --- |
| CertPSU-WPPlugin | `certpsu-connector-<version>.zip` | `certpsu-connector/certpsu-connector.php` |
| CertPSU-WPPlugin | `certpsu-tutorlms-<version>.zip` | `certpsu-tutorlms/certpsu-tutorlms.php` |
| Authorizenter | `authorizenter-core.zip` | `authorizenter-core/authorizenter-core.php` |
| Authorizenter | `authorizenter-ui.zip` | `authorizenter-ui/authorizenter-ui.php` |
| BIAPSU-ProfileSync-WPPlugin | `biapsu-profilesync.zip` | `biapsu-profilesync/biapsu-profilesync.php` |
| TutorLMS-Analytics | `tutorlms-analytics.zip` | `tutorlms-analytics/tutorlms-analytics.php` |
| TutorLMS-DeleteStudentOnDeleteUser | `tutor-user-cleanup.zip` | `tutor-user-cleanup/tutor-user-cleanup.php` |
| TutorLMS-LessonDuplicator | `tutor-lms-curriculum-duplicator-<version>.zip` | `tutor-lms-curriculum-duplicator/tutor-lms-curriculum-duplicator.php` |
| TutorLMS-CoursePrerequisites | `tutor-learning-paths.zip` | `tutor-learning-paths/tutor-learning-paths.php` |
| TutorLMS-CourseBundle | `tutor-course-bundles.zip` | `tutor-course-bundles/tutor-course-bundles.php` |
| BIA-Learn-WPTheme | `bia-learn.zip` | theme stylesheet `bia-learn` |

**Always read the Release asset listing first**; filenames and ZIP contents can change. There are 9 repositories but **11 ZIPs**: CertPSU and Authorizenter each have two plugins. Preserve the existing plugin activation state; the table above represents the 2026-11-10 installation and is not a general instruction to activate newly discovered plugins.

## Procedure

1. **GitHub verification:** For each repository, inspect `GET /repos/wachiravit-thitagran/<repo>/releases/latest`. Check `tag_name`, `draft=false`, `prerelease=false`, Release `assets[].browser_download_url`, SHA/target commit, and the relevant CI / Release workflow results. If the latest commit is intentionally `[skip release]`, use a manually dispatched Release workflow *only after verifying CI*; check that a newer Release was actually published. Do not infer success from a green workflow alone.
2. **WordPress baseline:** With BIA E-Learning WordPress MCP, call `wordpress/plugin-list` and `wordpress/theme-list` (ability parameters `{}`). Record versions, installation paths, active state, and current site URL. Only install genuinely newer, reviewed, compatible assets.
3. **Install plugins via HTTPS Release ZIP:** Invoke the MCP adapter `mcp_adapter_execute_ability` with:
   ```json
   {
     "ability_name": "wordpress/plugin-install-package",
     "parameters": {
       "package_url": "https://github.com/wachiravit-thitagran/<repo>/releases/download/<tag>/<asset>.zip",
       "overwrite": true,
       "activate": true
     }
   }
   ```
   Use the **actual** asset URL from GitHub, not a fabricated URL; `activate:true` applies to the plugins known to be active before the deployment. Inspect `success`, `data.plugin_file`, `data.version`, `data.overwritten`, and `data.activated` for each. Stop and investigate on failure. Install Auth Core before Auth UI; CertPSU Connector before CertPSU TutorLMS Bridge.
4. **Install the theme:** Invoke `wordpress/theme-install-package` with `package_url` for the verified `bia-learn.zip`, `overwrite:true`, `activate:true` (because `bia-learn` was already the active theme). Check `data.stylesheet == "bia-learn"`, version and active status.
5. **Post-deploy inventory:** Call `wordpress/plugin-list` and `wordpress/theme-list` again, verify *all 11* exact target versions and that the previously active plugins/theme remain active. Call `core/get-site-info` to ensure the WordPress MCP/site responds. Smoke-check `authorizenter/list-providers` and `biapsu-profilesync/get-status` for the authentication integration.
6. **Functional acceptance (separate from package installation):** Test login/OAuth in a real browser, EP1–EP4 prerequisites and enrollment restrictions, Tutor LMS course access, analytics, and CertPSU issuance/queue as applicable. Do **not** report end-to-end success if only inventories and ability calls were tested. Record results and any rollback actions.

## Known environment constraints and fallback

- WordPress Core `wordpress/plugin-update` was observed failing with `Call to undefined function request_filesystem_credentials()`; it was **not** the successful production update method.
- The server has previously had outbound cURL/firewall restrictions. Nevertheless, on **2026-11-10**, the WordPress MCP abilities `wordpress/plugin-install-package` and `wordpress/theme-install-package` **successfully downloaded the public GitHub Release ZIP URLs** for this deployment. Attempt them first, but never assume they will work forever.
- If server-side package download fails, the registered `wordpress/plugin-install-upload` ability can receive a **locally obtained, verified** ZIP as `zip_base64` plus `expected_sha256` and `plugin_slug`, with `overwrite:true`. Keep within the ability's limits (12 MiB decoded in the verified schema). Do not pass a URL into `zip_base64`, invent hashes, or claim success without a verified response. There was **no theme ZIP-upload ability** in the 2026-11-10 inventory; use an approved alternate deployment channel for themes if the theme package URL fails.
- If either install call returns an error, check permissions, package URL/asset existence, ZIP root structure, PHP requirements, activation state, and the WordPress filesystem. Do not repeatedly overwrite blindly.
- In production, do not deploy merely because a Release exists: preflight backups, dependency ordering, tests and post-deploy verification still apply.

## Successful 2026-11-10 deployment record

| Component | Verified production version |
| --- | --- |
| CertPSU Connector / TutorLMS Bridge | 0.1.40 / 0.1.40 |
| Authorizenter Core / UI | 1.0.60 / 1.0.60 |
| BIA PSU ProfileSync | 0.1.13 |
| TutorLMS Analytics | 2.0.14 |
| Tutor User Cleanup | 1.1.1 |
| Tutor LMS Curriculum Duplicator | 1.1.4 |
| Tutor Learning Paths (Course Prerequisites) | 1.0.22 |
| Tutor Course Bundles | 1.0.4 |
| BIA Learn Theme | 1.0.111 |

All 10 installed custom plugin files and the active theme were confirmed active via WordPress inventory. Basic MCP connectivity, site info, auth-provider listing and ProfileSync status checks passed. Full enrollment, OAuth browser flow and certificate end-to-end tests **were not performed** in that session.

## For the next ChatGPT deployment

Read this document and fresh GitHub Releases **first**. Compare with fresh WordPress inventory, use the above ability-based ZIP installation, and verify results. Version numbers and URLs in this log are historical, not evergreen deployment targets. If a user asks to deploy only changed repositories, skip components already at target version.

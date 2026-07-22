# Authorship Box

WordPress plugin for creating reusable Author profiles — with full schema.org/Person structured data — and attaching them to posts, pages, or any custom post type. See [readme.txt](readme.txt) for the full feature/user-facing description (WordPress.org format).

## Development

- **Local environment:** any local WordPress setup works (LocalWP, `wp-env`, etc.) — symlink or copy this folder into `wp-content/plugins/`.
- **Dev tooling** (PHPCS with WordPress Coding Standards) is managed by Composer and is dev-only — it is **not** required to run the plugin:
  ```
  composer install
  composer run lint
  ```
- **Runtime dependency:** [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) is vendored directly under `includes/lib/plugin-update-checker/` (committed to git, not Composer-managed) so the plugin works with no build step when installed on a live site.

## Releasing an update

1. Bump the `Version:` header in `authorship-box.php`.
2. Commit, then tag the commit `vX.Y.Z` (matching the version header) and push the tag:
   ```
   git tag vX.Y.Z
   git push origin vX.Y.Z
   ```
3. GitHub Actions (`.github/workflows/release.yml`) builds a clean zip (via `git archive`, excluding dev-only files listed in `.gitattributes`) and attaches it to a new GitHub Release.
4. Sites running the plugin pick up the update automatically via Plugin Update Checker within ~12 hours, or immediately via "Check for updates" on the Plugins page.

## CI

`.github/workflows/ci.yml` runs a PHP syntax check across supported PHP versions and PHPCS/WPCS linting on every push/PR to `main`.

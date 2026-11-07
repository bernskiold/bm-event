# Changelog

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 2.0.0 - 2026-11-07

### Requirements

- **PHP 8.2** or later and **WordPress 7.0** or later are now required. Tested up to WordPress 7.1.

### Breaking

- jQuery, select2, js-cookie and moment-timezone are gone. The timezone selector and dropdowns are native `<select>` elements (no select2 search box), and the time features use `Intl`.
- The `BMEvent`, `BMELiveViewer` and `BMETimeSwitcher` JavaScript globals are removed; shortcode scripts are script modules.
- The Track Timetable block now has `block.json` (API version 3), gains the standard `wp-block-bm-track-timetable` class and custom classes, and no longer outputs an empty `id=""`.

### Changed

- No longer depends on `wp-plugin-base` or `block-plugin-support`, and no longer relies on a shared `wp-content/vendor` autoloader.
- Build moved from Laravel Mix to `@wordpress/scripts` 37; built files are committed.
- Plugin activation grants the plugin's capabilities to the core roles (it was never hooked up before).

### Fixed

- A fatal error on single-site installs (a multisite-only function was called).
- Creating or updating posts through the data stores caused a fatal error, and created posts had no title.
- The `speakers` REST field returned empty values, and `Taxonomy::create()` always threw.
- Theme template overrides were never found.
- An invalid timezone cookie or a fractional UTC offset crashed the session grid.
- The global post wasn't reset after the block's loop.
- `block_categories` replaced with `block_categories_all`, plus PHP 8 deprecation fixes.

### Development

- PHPUnit (71 tests, single site and multisite) and Vitest suites on PHP 8.2–8.5 × WordPress 7.0 and latest; Node 26; WPCS 3; shared Bernskiold workflows.

## [1.2.4] - 2022-11-14

### Added

- Add customizer setting for the registration URL

### Changed

- Use the theme mod setting for the registration URL instead of shortcode setting, this to make the timezone switcher work

### Removed

- Removed shortcode setting for registration URL

## [1.2.3] - 2022-11-14

### Added

- Added timezone switcher
- Added section grid
- Added liveview script
- Added new datetime fields for the event

## [1.2.2] - 2021-07-09

### Added

- Added support for PHP 8. (#9)

## [1.2.1] - 2021-05-11

### Fixed

- Build failed because of incorrect syntax.

## [1.2.0] - 2021-05-10

### Added

- Live streaming URL capability.
- Prev/next URL getting

### Fixed

- Times would display in the incorrect timezone.

## [1.1.1] - 2020-07-19

### Changed

- Now using the plugin base with helper features.

### Fixed

- Fatal error where required function was missing.

## [1.1.0] - 2020-07-05

### Added

- Timezone selector support
- Grid view style option
- Support for loading the package as a plugin via composer.

### Removed

- Husky npm lint checker as it interferes with other composer-loaded projects.
- Dist built assets from the committed VCS.

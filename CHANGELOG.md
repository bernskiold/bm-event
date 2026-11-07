# Changelog

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

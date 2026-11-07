# BM Event
Functionality to help you market and manage an event. From showing speakers to a live agenda for one or multiple days.

Requires WordPress 7.0, PHP 8.2 and Advanced Custom Fields.

## Development

Use Node 26 (see `.nvmrc`) and Composer.

```sh
npm install && composer install
npm run build   # Build the block, scripts and styles with @wordpress/scripts.
npm run dev     # Rebuild on change.
npm run lint    # Lint JS, styles and package.json.
composer lint   # PHP_CodeSniffer with WordPress Coding Standards and PHPCompatibilityWP.
```

The built files in `dist/` and `assets/*/dist/` are committed so that the plugin works when installed from git.

## Coding Style
We exclusively try and use the WordPress coding practices for all languages,
which includes PHP, HTML, CSS and JavaScript.

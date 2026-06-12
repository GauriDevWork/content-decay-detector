# Changelog

All notable changes to Content Decay Detector are documented here.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [0.1.0] — 2026-06-12

### Added

- Plugin scaffold with PSR-4 autoloading via Composer
- Custom `wp_decay_snapshots` database table using `dbDelta()`
- `PostSnapshot` model class handling all DB reads and writes
- `Settings` class wrapping all `get_option()`/`update_option()` calls
- Admin settings page under Settings menu — threshold, frequency, email toggle
- `Scanner` class with WP Cron job running weekly decay scan
- Batched post processing — 50 posts per batch to prevent timeouts
- Decay scoring algorithm — compares current vs previous traffic snapshot, returns 0–100
- Rule-based `Suggestions` engine — 5 checks: stale title, old content, no internal links, no images, short content
- REST API endpoint — `GET /wp-json/content-decay/v1/reports` with nonce authentication
- `WP_List_Table` report page under Tools menu with sortable columns
- Score range filter on report page
- Bulk actions — Mark as Reviewed, Exclude from Scanning
- Dashboard widget showing top 5 decaying posts
- Weekly HTML email digest via `wp_mail()`
- Gutenberg block editor sidebar panel built with React and `@wordpress/scripts`
- WCAG 2.1 AA accessibility pass across all admin UI
- PHPUnit test suite — 12 passing tests covering scoring logic and settings
- Zero PHPCS violations on WordPress-Extra ruleset
- Full README with installation, configuration, API docs, and architecture

[0.1.0]: https://github.com/GauriDevWork/content-decay-detector/releases/tag/v0.1.0
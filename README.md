# Content Decay Detector

A WordPress plugin that detects decaying content by tracking traffic snapshots, scoring posts, and surfacing actionable fixes before rankings drop.

---

## Problem

WordPress site owners publish blog posts and pages, then forget about them. Over time, traffic drops as content becomes outdated — old keywords, stale statistics, broken structures, missing internal links.

Most site owners have no way to know which posts are decaying until Google Search Console shows a significant ranking drop, by which time it is often too late.

There is no existing WordPress plugin that proactively identifies decaying content, scores it, and suggests specific actionable fixes before the damage is done.

---

## Solution

Content Decay Detector runs a weekly background scan across all published posts, compares traffic snapshots over time, assigns a decay score to each post, and surfaces rule-based suggestions for improvement.

### Features

- **Automatic weekly scanning** via WP Cron — processes posts in batches to avoid timeouts
- **Decay scoring (0–100)** — compares current traffic against previous snapshot
- **Rule-based suggestions** — refresh title, add internal links, update statistics, expand content
- **Admin report table** — sortable by decay score with score range filter
- **Dashboard widget** — top 5 most decayed posts at a glance
- **Weekly email digest** — sends flagged posts to admin email automatically
- **REST API endpoint** — `GET /wp-json/content-decay/v1/reports` for external integrations
- **Gutenberg sidebar panel** — shows decay score inside the block editor for the post being edited
- **Bulk actions** — mark posts as reviewed or exclude from scanning

---

## Installation

1. Download or clone the repository into `wp-content/plugins/content-decay-detector/`
2. Run `composer install` to install PHP dependencies
3. Run `npm install && npm run build` to compile JavaScript assets
4. Activate the plugin from **WP Admin → Plugins**
5. The `wp_decay_snapshots` database table is created automatically on activation

### Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- Composer

---

## Configuration

Navigate to **WP Admin → Settings → Decay Detector** to configure:

| Setting | Description | Default |
|---|---|---|
| Decay Threshold (%) | Posts that have lost more than this percentage of traffic are flagged | 30% |
| Scan Frequency | How often the plugin scans posts for decay | Weekly |
| Email Notifications | Send a weekly email digest to the admin | Enabled |

---

## Usage

### Viewing the Report

Navigate to **WP Admin → Tools → Decay Report** to see all flagged posts.

- **Decay Score** — colored badge showing post health (green = healthy, red = decaying)
- **Suggestions** — specific actionable fixes for each post
- **Filter** — filter posts by score range using the min/max filter form
- **Bulk actions** — select multiple posts and mark as reviewed or exclude from scanning

### Dashboard Widget

The WordPress admin dashboard shows a **Content Decay** widget with the top 5 most decayed posts and a link to the full report.

### Gutenberg Panel

When editing a post in the block editor, open the **Content Decay** panel from the sidebar to see the current decay score and suggestions for that specific post.

### REST API

The plugin exposes a REST API endpoint for external integrations:

```
GET /wp-json/content-decay/v1/reports
```

**Authentication:** Requires a logged-in user with `manage_options` capability. Pass the nonce via `X-WP-Nonce` header.

**Parameters:**

| Parameter | Type | Description | Default |
|---|---|---|---|
| threshold | integer | Return posts below this decay score | Settings value |
| limit | integer | Maximum number of results (1–100) | 20 |

**Example response:**

```json
[
  {
    "post_id": 42,
    "post_title": "10 Tips for Better SEO",
    "post_url": "https://example.com/seo-tips/",
    "decay_score": 23,
    "traffic_score": 187,
    "snapshot_date": "2026-06-01",
    "suggestions": [
      "Refresh the post title with current keywords.",
      "Update statistics and outdated information in the content.",
      "Add internal links to related posts."
    ],
    "is_reviewed": false
  }
]
```

---

## Running Tests

```bash
vendor/bin/phpunit
```

All tests are located in the `tests/` directory and cover:

- Decay score calculation logic
- Settings defaults and validation
- Boundary conditions and edge cases

---

## Code Standards

The plugin follows the WordPress coding standard via PHPCS:

```bash
vendor/bin/phpcs
vendor/bin/phpcbf
```

Zero errors and zero warnings on the WordPress-Extra ruleset.

---

## Architecture

The plugin follows a single-responsibility OOP architecture with PSR-4 autoloading via Composer. No procedural code outside the main plugin file entry point.

| File | Purpose |
|---|---|
| `content-decay-detector.php` | Main plugin file — header, constants, bootstrap |
| `includes/Plugin.php` | Main class — registers all hooks |
| `includes/Installer.php` | Activation — creates DB table, sets defaults |
| `includes/Settings.php` | Wraps all `get_option()`/`update_option()` calls |
| `includes/PostSnapshot.php` | Model class for `wp_decay_snapshots` table |
| `includes/Scanner.php` | WP Cron job — scans posts, scores decay |
| `includes/Suggestions.php` | Rule-based suggestion generator |
| `includes/Email.php` | Weekly digest email builder and sender |
| `includes/REST/Reports.php` | REST API endpoint |
| `includes/Admin/Report_Table.php` | WP_List_Table subclass for admin report |
| `includes/Admin/Report_Page.php` | Admin page registration and rendering |
| `includes/Admin/Dashboard_Widget.php` | Dashboard widget |
| `includes/Admin/Block_Editor.php` | Gutenberg sidebar panel enqueue |
| `src/index.js` | React sidebar panel source |

---

## Database Schema

One custom table: `wp_decay_snapshots`

| Column | Type | Description |
|---|---|---|
| `id` | BIGINT UNSIGNED | Primary key |
| `post_id` | BIGINT UNSIGNED | FK to wp_posts.ID |
| `snapshot_date` | DATE | Date snapshot was taken |
| `traffic_score` | INT | Raw traffic score |
| `decay_score` | TINYINT UNSIGNED | Calculated 0–100 score |
| `suggestions` | LONGTEXT | JSON array of suggestion strings |
| `is_reviewed` | TINYINT | 0 = flagged, 1 = reviewed |
| `created_at` | DATETIME | Row creation timestamp |

---

## Roadmap

- [ ] Google Search Console API integration for real traffic data
- [ ] Per-post decay history chart
- [ ] Slack notification integration
- [ ] WP-CLI command to trigger manual scan
- [ ] Multisite support

---

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for details.

---

## Author

Built by [Gauri](https://github.com/GauriDevWork) as a portfolio project claimed from the [WPFolks Ideas board](https://wpfolks.com).

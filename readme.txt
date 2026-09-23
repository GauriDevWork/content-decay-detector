=== Decaywatcher ===
Contributors: gauri87
Tags: content, seo, decay, maintenance, freshness
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Decaywatcher scores your posts for content decay using age, word count, and engagement — then surfaces actionable fixes.

== Description ==

WordPress site owners publish blog posts and pages, then forget about them. Over time, content becomes outdated — old statistics, missing internal links, thin word counts, and low engagement signal that a post is losing value.

Decaywatcher runs a weekly background scan across all published posts and assigns each one a content health score (0–100) based on real WordPress data:

* **Post age** — how recently the post was updated
* **Word count** — whether the content is substantial enough
* **Comment count** — whether readers are engaging with the post

Posts that fall below your configured threshold are flagged in an admin report with rule-based suggestions for improvement — before rankings drop.

The source code, including uncompiled assets and build tools, is publicly available at:
https://github.com/GauriDevWork/content-decay-detector

= Features =

* **Automatic weekly scanning** via WP Cron — processes posts in batches to avoid timeouts
* **Content health scoring (0–100)** — based on post age, word count, and comment count
* **Rule-based suggestions** — refresh title, add internal links, update statistics, expand content
* **Admin report table** — sortable by score with score range filter
* **Dashboard widget** — top 5 most decayed posts at a glance
* **Weekly email digest** — sends flagged posts to admin email automatically
* **REST API endpoint** — GET /wp-json/content-decay/v1/reports for external integrations
* **Gutenberg sidebar panel** — shows decay score inside the block editor for the post being edited
* **Bulk actions** — mark posts as reviewed or exclude from scanning

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install directly through the WordPress plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress Admin.
3. The `wp_decay_snapshots` database table is created automatically on activation.
4. Navigate to **Settings → Decaywatcher** to configure the decay threshold and email notifications.
5. Navigate to **Tools → Decay Report** to view flagged posts.

== Frequently Asked Questions ==

= How is the content health score calculated? =

The score (0–100) is calculated from three real WordPress data points: how recently the post was updated (up to 50 points), the post word count (up to 30 points), and the number of comments (up to 20 points). No external API or third-party service is required.

= Will this plugin slow down my site? =

No. The scan runs via WP Cron in the background on a weekly schedule. It processes posts in batches of 50 to avoid memory and timeout issues. Nothing runs on the front end.

= What happens to my data if I uninstall the plugin? =

All plugin data is removed on uninstall — the database table, all options, and the scheduled cron event are deleted automatically.

= What does the decay score mean? =

A score of 100 means the post is healthy — recently updated, substantial content, good engagement. A score below your configured threshold (default 30) means the post needs attention.

= Does this work with Multisite? =

Multisite support is planned for a future release. The current version works on single-site installations only.

== Screenshots ==

1. Admin report table showing decay scores and suggestions for each post.
2. Dashboard widget displaying the top 5 most decayed posts.
3. Gutenberg sidebar panel showing decay score while editing a post.
4. Settings page with threshold, frequency, and email notification options.

== Changelog ==

= 0.1.0 =
* Initial release.
* Weekly WP Cron decay scan with batched post processing.
* Content health scoring based on post age, word count, and comment count.
* Rule-based suggestion engine with 5 checks.
* Admin report page with sortable columns, score filter, and bulk actions.
* Dashboard widget showing top 5 decaying posts.
* Weekly HTML email digest.
* REST API endpoint with nonce authentication.
* Gutenberg block editor sidebar panel built with React.
* PHPUnit test suite with 12 passing tests.
* Zero PHPCS violations on WordPress-Extra ruleset.

== Upgrade Notice ==

= 0.1.0 =
Initial release. No upgrade steps required.
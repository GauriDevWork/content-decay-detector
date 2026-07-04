=== Content Decay Detector ===
Contributors: gauri87
Tags: content, seo, decay, traffic, maintenance
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Detects decaying WordPress content by tracking traffic snapshots, scoring posts, and suggesting actionable fixes before rankings drop.

== Description ==

WordPress site owners publish blog posts and pages, then forget about them. Over time, traffic drops as content becomes outdated — old keywords, stale statistics, broken structures, missing internal links.

Most site owners have no way to know which posts are decaying until Google Search Console shows a significant ranking drop, by which time it is often too late.

Content Decay Detector runs a weekly background scan across all published posts, compares traffic snapshots over time, assigns a decay score to each post, and surfaces rule-based suggestions for improvement — before the damage is done.

= Features =

* **Automatic weekly scanning** via WP Cron — processes posts in batches to avoid timeouts
* **Decay scoring (0–100)** — compares current traffic against previous snapshot
* **Rule-based suggestions** — refresh title, add internal links, update statistics, expand content
* **Admin report table** — sortable by decay score with score range filter
* **Dashboard widget** — top 5 most decayed posts at a glance
* **Weekly email digest** — sends flagged posts to admin email automatically
* **REST API endpoint** — GET /wp-json/content-decay/v1/reports for external integrations
* **Gutenberg sidebar panel** — shows decay score inside the block editor for the post being edited
* **Bulk actions** — mark posts as reviewed or exclude from scanning

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install directly through the WordPress plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress Admin.
3. The `wp_decay_snapshots` database table is created automatically on activation.
4. Navigate to **Settings → Decay Detector** to configure the decay threshold and email notifications.
5. Navigate to **Tools → Decay Report** to view flagged posts.

== Frequently Asked Questions ==

= Where does the traffic data come from? =

The plugin calculates a content health score using real WordPress data — post age, word count, and comment count. No external API or third-party service is required.

= Will this plugin slow down my site? =

No. The scan runs via WP Cron in the background on a weekly schedule. It processes posts in batches of 50 to avoid memory and timeout issues. Nothing runs on the front end.

= What happens to my data if I uninstall the plugin? =

All plugin data is removed on uninstall — the database table, all options, and the scheduled cron event are deleted automatically.

= What does the decay score mean? =

A score of 100 means the post is healthy — traffic is stable or growing. A score below your configured threshold (default 30%) means the post has lost significant traffic and needs attention.

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
* Decay scoring algorithm comparing current vs previous traffic snapshots.
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
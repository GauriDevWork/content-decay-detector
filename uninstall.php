<?php
/**
 * Uninstall Content Decay Detector.
 *
 * Runs automatically when the plugin is deleted from WP Admin.
 * Removes all plugin data: database table, options, and cron events.
 *
 * @package ContentDecayDetector
 */

// Security check — block direct access.
// This constant is only defined by WordPress during uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Drop the custom snapshots table.
$table_name = $wpdb->prefix . 'decay_snapshots';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// Delete all plugin options.
delete_option( 'cdd_version' );
delete_option( 'cdd_decay_threshold' );
delete_option( 'cdd_scan_frequency' );
delete_option( 'cdd_email_notifications' );

// Clear the scheduled cron event.
$timestamp = wp_next_scheduled( 'cdd_weekly_decay_scan' );
if ( $timestamp ) {
	wp_unschedule_event( $timestamp, 'cdd_weekly_decay_scan' );
}
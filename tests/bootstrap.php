<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package ContentDecayDetector
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// In-memory options store for tests.
$GLOBALS['cdd_test_options'] = [];

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $key, $default = false ) {
        if ( isset( $GLOBALS['cdd_test_options'][ $key ] ) ) {
            return $GLOBALS['cdd_test_options'][ $key ];
        }
        return $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $key, $value ) {
        $GLOBALS['cdd_test_options'][ $key ] = $value;
        return true;
    }
}

if ( ! function_exists( 'get_posts' ) ) {
    function get_posts( $args = [] ) { return []; }
}

if ( ! function_exists( 'get_post' ) ) {
    function get_post( $id = null ) { return null; }
}

if ( ! function_exists( 'wp_rand' ) ) {
    function wp_rand( $min = 0, $max = 0 ) { return rand( $min, $max ); }
}

if ( ! function_exists( 'current_time' ) ) {
    function current_time( $format ) { return date( $format ); }
}

if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data ) { return json_encode( $data ); }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $args = 1 ) { return true; }
}

if ( ! function_exists( 'wp_next_scheduled' ) ) {
    function wp_next_scheduled( $hook ) { return false; }
}

if ( ! function_exists( 'wp_schedule_event' ) ) {
    function wp_schedule_event( $time, $recurrence, $hook ) { return true; }
}

if ( ! function_exists( 'wp_unschedule_event' ) ) {
    function wp_unschedule_event( $time, $hook ) { return true; }
}

// Mock $wpdb global.
global $wpdb;
$wpdb = new class {
    public string $prefix = 'wp_';

    public function prepare( $query, ...$args ) {
        return vsprintf( str_replace( '%d', '%d', $query ), $args );
    }

    public function get_results( $query, $output = 'OBJECT' ) { return []; }

    public function get_row( $query, $output = 'OBJECT' ) { return null; }

    public function get_var( $query ) { return null; }

    public function insert( $table, $data, $format = null ) { return false; }

    public function delete( $table, $where, $where_format = null ) { return false; }
};
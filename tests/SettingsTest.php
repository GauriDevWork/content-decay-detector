<?php
/**
 * Tests for Settings class.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector\Tests;

use ContentDecayDetector\Settings;
use PHPUnit\Framework\TestCase;

/**
 * Class SettingsTest
 *
 * Tests default values returned by the Settings class.
 */
class SettingsTest extends TestCase {

    /**
     * Settings instance.
     *
     * @var Settings
     */
    private Settings $settings;

    /**
     * Set up test instance.
     *
     * @return void
     */
    protected function setUp(): void {
        $this->settings = new Settings();
    }

    /**
     * Test default decay threshold is 30.
     *
     * @return void
     */
    public function test_default_threshold_is_30(): void {
        $this->assertSame( 30, $this->settings->get_threshold() );
    }

    /**
     * Test default scan frequency is weekly.
     *
     * @return void
     */
    public function test_default_scan_frequency_is_weekly(): void {
        $this->assertSame( 'weekly', $this->settings->get_scan_frequency() );
    }

    /**
     * Test email notifications enabled by default.
     *
     * @return void
     */
    public function test_email_notifications_enabled_by_default(): void {
        $this->assertTrue( $this->settings->is_email_enabled() );
    }

    /**
     * Test update_scan_frequency rejects invalid values.
     *
     * @return void
     */
    public function test_update_scan_frequency_rejects_invalid_value(): void {
        $result = $this->settings->update_scan_frequency( 'hourly' );
        $this->assertFalse( $result );
    }

    /**
     * Test threshold is clamped to minimum of 1.
     *
     * @return void
     */
    public function test_threshold_clamped_to_minimum_of_1(): void {
        $this->settings->update_threshold( 0 );
        $this->assertSame( 1, $this->settings->get_threshold() );
    }

    /**
     * Test threshold is clamped to maximum of 100.
     *
     * @return void
     */
    public function test_threshold_clamped_to_maximum_of_100(): void {
        $this->settings->update_threshold( 150 );
        $this->assertSame( 100, $this->settings->get_threshold() );
    }
}
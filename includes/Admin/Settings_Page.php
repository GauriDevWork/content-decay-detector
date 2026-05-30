<?php
/**
 * Admin settings page.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings_Page
 *
 * Registers the plugin settings page and all settings fields
 * using the WordPress Settings API.
 */
class Settings_Page {

    /**
     * Register hooks.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    /**
     * Add settings page under the Settings menu.
     *
     * @return void
     */
    public function add_menu_page(): void {
        add_options_page(
            __( 'Content Decay Detector', 'content-decay-detector' ),
            __( 'Decay Detector', 'content-decay-detector' ),
            'manage_options',
            'content-decay-detector',
            [ $this, 'render_page' ]
        );
    }

    /**
     * Register all plugin settings, sections and fields.
     *
     * @return void
     */
    public function register_settings(): void {
        // Register settings.
        register_setting(
            'cdd_settings_group',
            'cdd_decay_threshold',
            [
                'type'              => 'integer',
                'sanitize_callback' => [ $this, 'sanitize_threshold' ],
                'default'           => 30,
            ]
        );

        register_setting(
            'cdd_settings_group',
            'cdd_scan_frequency',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'weekly',
            ]
        );

        register_setting(
            'cdd_settings_group',
            'cdd_email_notifications',
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => true,
            ]
        );

        // Add settings section.
        add_settings_section(
            'cdd_main_section',
            __( 'Detection Settings', 'content-decay-detector' ),
            [ $this, 'render_section_description' ],
            'content-decay-detector'
        );

        // Add settings fields.
        add_settings_field(
            'cdd_decay_threshold',
            __( 'Decay Threshold (%)', 'content-decay-detector' ),
            [ $this, 'render_threshold_field' ],
            'content-decay-detector',
            'cdd_main_section'
        );

        add_settings_field(
            'cdd_scan_frequency',
            __( 'Scan Frequency', 'content-decay-detector' ),
            [ $this, 'render_frequency_field' ],
            'content-decay-detector',
            'cdd_main_section'
        );

        add_settings_field(
            'cdd_email_notifications',
            __( 'Email Notifications', 'content-decay-detector' ),
            [ $this, 'render_email_field' ],
            'content-decay-detector',
            'cdd_main_section'
        );
    }

    /**
     * Render the section description.
     *
     * @return void
     */
    public function render_section_description(): void {
        echo '<p>' . esc_html__( 'Configure how Content Decay Detector scans and reports decaying content.', 'content-decay-detector' ) . '</p>';
    }

    /**
     * Render the decay threshold field.
     *
     * @return void
     */
    public function render_threshold_field(): void {
        $value = get_option( 'cdd_decay_threshold', 30 );
        echo '<input type="number" name="cdd_decay_threshold" value="' . esc_attr( $value ) . '" min="1" max="100" class="small-text" />';
        echo '<p class="description">' . esc_html__( 'Flag posts that have lost this percentage of traffic compared to their peak.', 'content-decay-detector' ) . '</p>';
    }

    /**
     * Render the scan frequency field.
     *
     * @return void
     */
    public function render_frequency_field(): void {
        $value = get_option( 'cdd_scan_frequency', 'weekly' );
        $options = [
            'daily'   => __( 'Daily', 'content-decay-detector' ),
            'weekly'  => __( 'Weekly', 'content-decay-detector' ),
            'monthly' => __( 'Monthly', 'content-decay-detector' ),
        ];
        echo '<select name="cdd_scan_frequency">';
        foreach ( $options as $key => $label ) {
            echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__( 'How often should the plugin scan your posts for decay.', 'content-decay-detector' ) . '</p>';
    }

    /**
     * Render the email notifications field.
     *
     * @return void
     */
    public function render_email_field(): void {
        $value = get_option( 'cdd_email_notifications', true );
        echo '<input type="checkbox" name="cdd_email_notifications" value="1" ' . checked( 1, $value, false ) . ' />';
        echo '<p class="description">' . esc_html__( 'Send a weekly email digest of decaying posts to the admin.', 'content-decay-detector' ) . '</p>';
    }

    /**
     * Render the full settings page.
     *
     * @return void
     */
    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'cdd_settings_group' );
                do_settings_sections( 'content-decay-detector' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Sanitize the decay threshold value.
     *
     * @param mixed $value The raw input value.
     *
     * @return int Sanitized integer between 1 and 100.
     */
    public function sanitize_threshold( mixed $value ): int {
        $value = absint( $value );
        return max( 1, min( 100, $value ) );
    }

    /**
     * Sanitize a checkbox value.
     *
     * @param mixed $value The raw input value.
     *
     * @return bool True if checked, false otherwise.
     */
    public function sanitize_checkbox( mixed $value ): bool {
        return (bool) $value;
    }
}
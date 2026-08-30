<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Idle User Logout dashboard widget.
 */
class IUL_DASHBOARD {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'initialize_iul_dashboard'));
    }

    /**
     * Register dashboard widget.
     *
     * @return void
     */
    public function initialize_iul_dashboard() {
        wp_add_dashboard_widget(
            'iul_dashboard_widget',
            '<span class="dashicons dashicons-admin-users"></span> ' . __('Idle User Logout Stats', 'iul'),
            array($this, 'show_iul_dashboard')
        );
    }

    /**
     * Display dashboard widget content.
     *
     * @return void
     */
    public function show_iul_dashboard() {
        $iul_behavior = get_option('iul_behavior');
        $iul_data = get_option('iul_data');
        $default_time = isset($iul_data['iul_idleTimeDuration']) ? $iul_data['iul_idleTimeDuration'] : '';

        if (empty($iul_behavior)) {
            echo '<p>' . esc_html__('No behavior has been defined yet', 'iul') . '</p>';
        } else {
            ?>
            <div class="wrap">
                <ul>
                    <?php foreach ($iul_behavior as $role => $behavior): ?>
                        <?php
                        if ($behavior['idle_action'] == 1) {
                            echo '<li><span class="dashicons dashicons-arrow-right"></span> ' . esc_html__('Bypass logout for', 'iul') . ' <strong>' . esc_html($role) . '</strong></li>';
                        } elseif ($behavior['idle_action'] == 2) {
                            echo '<li><span class="dashicons dashicons-arrow-right"></span> ' . esc_html__('Logout and redirect to Login page for', 'iul') . ' <strong>' . esc_html($role) . '</strong></li>';
                        } else {
                            $page = get_post($behavior['idle_page']);
                            $action_word = $this->get_action_by_opt($behavior['idle_action']);
                            $action = str_replace('#pagename', $page->post_title, $action_word);
                            echo '<li><span class="dashicons dashicons-arrow-right"></span> ' . esc_html($action) . ' ' . esc_html__('for', 'iul') . ' <strong>' . esc_html($role) . '</strong> ' . esc_html__('after', 'iul') . ' ' . esc_html(empty($behavior['idle_timer']) ? $default_time : $behavior['idle_timer']) . ' ' . esc_html__('seconds', 'iul') . '</li>';
                        }
                        ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php
        }
    }

    /**
     * Get action label by option value.
     *
     * @param int $num Option value.
     * @return string
     */
    public function get_action_by_opt($num) {
        $word = '';
        switch ($num) {
            case 1:
                $word = __('By pass logout', 'iul');
                break;

            case 2:
                $word = __('Logout user and redirect to login page', 'iul');
                break;

            case 3:
                $word = __('Logout user and redirect to #pagename page', 'iul');
                break;

            case 4:
                $word = __('Do not logout but show #pagename# page in popup', 'iul');
                break;

            case 5:
                $word = __('Do not logout but redirect to #pagename# page', 'iul');
                break;

            default:
                $word = '';
        }

        return $word;
    }
}

<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Idle User Logout AJAX and inline actions handler.
 */
class IUL_ACTIONS {

    /**
     * Plugin data.
     *
     * @var array
     */
    private $iul_data;

    /**
     * Behavior options.
     *
     * @var array
     */
    private $iul_behavior;

    /**
     * Popup page object.
     *
     * @var WP_Post|null
     */
    private $popup_page;

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('wp_ajax_logout_idle_user', array($this, 'logout_idle_user'));
        add_action('wp_ajax_update_user_time', array($this, 'update_user_time'));
        add_action('admin_head', array($this, 'start_iul_action'));
        add_action('wp_head', array($this, 'start_iul_action'));

        $this->popup_page = '';
        $this->iul_behavior = get_option('iul_behavior');
        $this->iul_data = get_option('iul_data');
    }

    /**
     * Output localized script data in the head.
     *
     * @return void
     */
    public function start_iul_action() {
        global $is_iphone;
        $is_mobile = false;
        if (wp_is_mobile() || $is_iphone) {
            $is_mobile = true;
        }

        if (!is_user_logged_in()) {
            return;
        }

        $output = '';
        $behavior = $this->iul_behavior;
        $default_data = $this->iul_data;

        $action = array();
        $action['action_type'] = '2';
        $action['timer'] = isset($default_data['iul_idleTimeDuration']) ? $default_data['iul_idleTimeDuration'] : '';

        if (is_admin() && isset($default_data['iul_disable_admin'])) {
            $action['disable_admin'] = true;
        }

        $user = wp_get_current_user();
        $roles = !empty($user->roles) ? $user->roles[0] : '';

        if (!empty($roles) && isset($behavior[$roles])) {
            $action['action_type'] = $behavior[$roles]['idle_action'];
            $action['action_value'] = '';

            if (in_array($action['action_type'], array('3', '4', '5'), true) && !empty($behavior[$roles]['idle_page'])) {
                $this->popup_page = get_post($behavior[$roles]['idle_page']);
                $url = get_permalink($this->popup_page->ID);
                if (!is_page($this->popup_page->ID)) {
                    $action['action_value'] = $url;
                } else {
                    $action['action_value'] = '';
                }
            }

            $action['timer'] = empty($behavior[$roles]['idle_timer']) ? $default_data['iul_idleTimeDuration'] : $behavior[$roles]['idle_timer'];
        }

        if (isset($action['action_type']) && $action['action_type'] === '4' && !empty($behavior[$roles]['idle_page']) && $this->popup_page) {
            $context = is_admin() ? 'admin' : 'frontend';
            $content = iul_get_modal_template($this->popup_page, $context);
            $output .= apply_filters('iul_modal_content', $content);
            $action['modal'] = $output;
        }

        $final_action = apply_filters('iul_action', $action);
        wp_localize_script('iul-script', 'iul', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('iul-nonce'),
            'actions' => $final_action,
            'is_mobile' => $is_mobile,
        ));
    }

    /**
     * AJAX handler to logout idle user.
     *
     * @return void
     */
    public function logout_idle_user() {
        check_ajax_referer('iul-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'User not logged in'));
        }

        do_action('uil_before_logout', get_current_user_id());
        delete_user_meta(get_current_user_id(), 'last_active_time');
        wp_logout();
        do_action('uil_after_logout');
        wp_send_json_success(true);
    }

    /**
     * AJAX handler to update user idle time.
     *
     * @return void
     */
    public function update_user_time() {
        check_ajax_referer('iul-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'User not logged in'));
        }

        if (!isset($_POST['callType'])) {
            wp_send_json_error(array('message' => 'Missing parameter'));
        }

        $type = sanitize_text_field(wp_unslash($_POST['callType']));

        if ($type === 'active') {
            $active_time = date('H:i:s');
            update_user_meta(get_current_user_id(), 'last_active_time', $active_time);
        } else {
            delete_user_meta(get_current_user_id(), 'last_active_time');
        }

        wp_send_json_success(true);
    }
}

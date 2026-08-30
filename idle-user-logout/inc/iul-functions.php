<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get modal template HTML.
 *
 * Allows themes to override by placing the template file
 * in the theme root. Falls back to the plugin template.
 *
 * @param WP_Post $popup_page Popup page object.
 * @param string  $context    'frontend' or 'admin'.
 * @return string
 */
function iul_get_modal_template($popup_page, $context = 'frontend') {
    $template = 'idle-user-logout-modal.php';

    if ($context === 'admin') {
        $template = 'idle-user-logout-admin-modal.php';
    }

    $located = locate_template($template, false, false);
    if ($located) {
        ob_start();
        include $located;
        return ob_get_clean();
    }

    ob_start();
    include IUL_PATH . 'templates/' . $template;
    return ob_get_clean();
}

/**
 * Execute the behavioral action for idle users.
 *
 * @return void
 */
function iul_execute_behavioural_action() {
    $behavior = get_option('iul_behavior');
    $default_data = get_option('iul_data');

    if (!isset($default_data['iul_disable_admin']) && !is_admin()) {
        $user = wp_get_current_user();
        $roles = !empty($user->roles) ? $user->roles[0] : '';

        if (!empty($roles) && isset($behavior[$roles])) {
            switch ($behavior[$roles]['idle_action']) {
                case '5':
                    if (isset($behavior[$roles]['idle_page'])) {
                        $popup_page = get_post($behavior[$roles]['idle_page']);
                        $url = get_permalink($popup_page->ID);
                        $url = apply_filters('iul_redirect_without_logout', $url);
                        wp_safe_redirect($url);
                        exit;
                    }
                    break;

                case '4':
                    if (isset($behavior[$roles]['idle_page'])) {
                        $popup_page = get_post($behavior[$roles]['idle_page']);
                        $output = iul_get_modal_template($popup_page, 'frontend');
                        $output = apply_filters('iul_modal_content', $output);
                        add_action('wp_footer', function () use ($output) {
                            iul_print_modal_script(json_encode($output));
                        });
                    }
                    break;

                case '3':
                    if (isset($behavior[$roles]['idle_page'])) {
                        $popup_page = get_post($behavior[$roles]['idle_page']);
                        $url = get_permalink($popup_page->ID);
                        $url = apply_filters('iul_redirect_with_logout', $url);
                        delete_user_meta(get_current_user_id(), 'last_active_time');
                        wp_clear_auth_cookie();
                        wp_safe_redirect($url);
                        exit;
                    }
                    break;

                case '2':
                default:
                    delete_user_meta(get_current_user_id(), 'last_active_time');
                    wp_clear_auth_cookie();
                    wp_safe_redirect(wp_login_url());
                    exit;
            }
        }
    }
}

/**
 * Print modal script for popup behavior.
 *
 * @param string $output Encoded modal content.
 * @return void
 */
function iul_print_modal_script($output) {
    ?>
    <script>
        var content = <?php echo $output; ?>;
        var popup_open = 0;
        jQuery(window).on('load', function () {
            if (content && popup_open === 0) {
                var modal = UIkit.modal.blockUI(content);
                popup_open = 1;
                jQuery('#close_modal').on('click', function (e) {
                    e.preventDefault();
                    modal.hide();
                    popup_open = 0;
                });
            }
        });
    </script>
    <?php
}

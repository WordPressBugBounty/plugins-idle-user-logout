<?php
/**
 * Plugin Name: Idle User Logout
 * Plugin URI: http://wordpress.org/extend/plugins/idle-user-logout/
 * Description: This plugin automatically logs out the user after a period of idle time. The time period can be configured from admin end.
 * Version: 3.1.1
 * Author: Abiral Neupane
 * Author URI: https://abrlnp.me
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

if (!defined('IUL_PATH')) {
    define('IUL_PATH', plugin_dir_path(__FILE__));
}

/**
 * Main plugin class.
 */
class IDLE_USER_LOGOUT {

    /**
     * Plugin instance.
     *
     * @var IDLE_USER_LOGOUT|null
     */
    private static $instance = null;

    /**
     * Get plugin instance.
     *
     * @return IDLE_USER_LOGOUT
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        register_activation_hook(__FILE__, array($this, 'iul_activate'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_image_size('popup-image', 545, 220, true);
        add_action('wp_enqueue_scripts', array($this, 'add_iul_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'add_iul_scripts'));
        add_action('init', array($this, 'iul_check_last_session'));
        add_action('admin_init', array($this, 'iul_check_last_session'));
    }

    /**
     * Load plugin textdomain.
     *
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain('iul', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Activate plugin and set default options.
     *
     * @return void
     */
    public static function iul_activate() {
        update_option('iul_data', array('iul_idleTimeDuration' => 20, 'iul_disable_admin' => true));
        update_option('iul_behavior', array());
    }

    /**
     * Enqueue scripts and styles.
     *
     * @return void
     */
    public function add_iul_scripts() {
        wp_register_script('jquery-idle', plugins_url('js/idle-timer.min.js', __FILE__), array('jquery'), '1.2.1', true);
        wp_register_script('uikit', plugins_url('js/uikit.min.js', __FILE__), array('jquery'), '1.2.1', true);

        if (is_user_logged_in()) {
            wp_enqueue_script('iul-script', plugins_url('js/script.js', __FILE__), array('jquery-idle', 'uikit'), '2.0', true);
            wp_enqueue_style('iul-style', plugins_url('css/style.css', __FILE__));
        }
    }

    /**
     * Check last session and enforce idle timeout.
     *
     * @return void
     */
    public function iul_check_last_session() {
        if (!is_user_logged_in()) {
            return;
        }

        $user = wp_get_current_user();
        $roles = !empty($user->roles) ? $user->roles[0] : '';
        $iul_data = get_option('iul_data');
        $iul_behavior = get_option('iul_behavior');
        $iul_disable_admin = isset($iul_data['iul_disable_admin']) ? $iul_data['iul_disable_admin'] : false;

        if (!$iul_disable_admin) {
            if (!is_admin()) {
                $last_active_time = get_user_meta(get_current_user_id(), 'last_active_time', true);
                $current_time = date('H:i:s');

                if ($last_active_time) {
                    $timer = empty($iul_behavior[$roles]['idle_timer']) ? $iul_data['iul_idleTimeDuration'] : $iul_behavior[$roles]['idle_timer'];
                    $diff = strtotime($current_time) - strtotime($last_active_time);
                    if ($diff >= $timer) {
                        iul_execute_behavioural_action();
                        delete_user_meta(get_current_user_id(), 'last_active_time');
                        do_action('uil_after_logout');
                    } else {
                        update_user_meta(get_current_user_id(), 'last_active_time', $current_time);
                    }
                } else {
                    update_user_meta(get_current_user_id(), 'last_active_time', $current_time);
                }
            } else {
                $current_time = date('H:i:s');
                update_user_meta(get_current_user_id(), 'last_active_time', $current_time);
            }
        }
    }
}

require_once IUL_PATH . '/inc/iul-functions.php';
require_once IUL_PATH . '/inc/admin/admin_menu.php';
require_once IUL_PATH . '/inc/admin/dashboard.php';
require_once IUL_PATH . '/inc/iul_actions.php';

$iul = IDLE_USER_LOGOUT::get_instance();
$admin_iul = new IUL_ADMIN();
$dashboard_iul = new IUL_DASHBOARD();
$iul_action = new IUL_ACTIONS();

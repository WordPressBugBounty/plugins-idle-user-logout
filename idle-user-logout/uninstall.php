<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('iul_data');
delete_option('iul_behavior');

<?php
/**
 * Created by PhpStorm.
 * User: Rubal
 * Date: 1/21/16
 * Time: 10:34 AM
 */
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

$delete_iul_data = 'iul_data';
$delete_iul_behavior = 'iul_behavior';

delete_option( $delete_iul_data );
delete_option( $delete_iul_behavior );
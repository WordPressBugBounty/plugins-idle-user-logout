<?php
/**
 * Idle User Logout modal/popup template for admin.
 *
 * Variables expected:
 * @var WP_Post $popup_page  Popup page object.
 */
?>

<div class="modal-content">
    <a href="javascript:void(0)" id="close_modal"><span class="dashicons dashicons-no"></span></a>
    <?php if (has_post_thumbnail($popup_page->ID)) : ?>
        <div class="featured">
            <?php echo get_the_post_thumbnail($popup_page->ID, 'popup-image'); ?>
        </div>
    <?php endif; ?>
    <h3><?php echo esc_html($popup_page->post_title); ?></h3>
    <?php echo wp_kses_post($popup_page->post_content); ?>
</div>

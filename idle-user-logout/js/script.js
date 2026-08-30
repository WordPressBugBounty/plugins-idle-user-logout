if (iul.is_mobile) {
    jQuery(document).ready(function () {
        main_script();
    });
} else {
    jQuery(window).on('load', function () {
        main_script();
    });
}

var iul_countdown_interval;
var iul_toast = null;
var popup_open = 0;

function main_script() {
    if (typeof(iul) != 'undefined') {
        var content = iul.actions.modal,
            timer = parseInt(iul.actions.timer) * 1000,
            action = iul.actions.action_type,
            action_value = iul.actions.action_value,
            disable_admin = iul.actions.disable_admin;

        if (!disable_admin) {
            jQuery(document).idleTimer(timer);

            jQuery(document).bind("active.idleTimer", function () {
                clearInterval(iul_countdown_interval);
                hide_iul_toast();
                idle_user_status_call('active');
            });

            jQuery(document).bind("idle.idleTimer", function () {
                idle_user_status_call('idle');
            });

            jQuery(document).bind("idle.idleTimer", function () {
                var action_type = parseInt(action);
                if ((action_type === 2 || action_type === 3) && parseInt(iul.actions.timer) >= 10) {
                    start_iul_countdown(action_type);
                } else {
                    execute_iul_action(action_type);
                }
            });
        }
    }
}

function start_iul_countdown(action_type) {
    var remaining = 10;
    show_iul_toast(remaining);

    iul_countdown_interval = setInterval(function () {
        remaining--;
        if (remaining > 0) {
            update_iul_toast(remaining);
        } else {
            clearInterval(iul_countdown_interval);
            hide_iul_toast();
            execute_iul_action(action_type);
        }
    }, 1000);
}

function execute_iul_action(action_type) {
    switch (action_type) {
        case 2:
            idle_user_logout_callback('', true);
            break;

        case 3:
            if (iul.actions.action_value) {
                idle_user_logout_callback(iul.actions.action_value, false);
            }
            break;

        case 4:
            if (iul.actions.modal && popup_open === 0) {
                var modal = UIkit.modal.blockUI(iul.actions.modal);
                popup_open = 1;
                jQuery('#close_modal').on('click', function (e) {
                    e.preventDefault();
                    modal.hide();
                    popup_open = 0;
                });
            }
            break;

        case 5:
            if (iul.actions.action_value) {
                window.location = iul.actions.action_value;
            }
            break;

        case 1:
        default:
            /* Do nothing */
    }
}

function show_iul_toast(seconds) {
    if (!iul_toast) {
        var toast_html = '<div id="iul-toast">' +
            '<div id="iul-toast-title">Warning</div>' +
            '<div id="iul-toast-message">You are about to be logged out in <span id="iul-toast-countdown">' + seconds + '</span> seconds</div>' +
            '<button id="iul-stay-logged-in">Stay logged in</button>' +
            '</div>';
        jQuery('body').append(toast_html);
        iul_toast = jQuery('#iul-toast');

        jQuery('#iul-stay-logged-in').on('click', function () {
            reset_iul_timer();
        });
    } else {
        jQuery('#iul-toast-countdown').text(seconds);
    }
}

function update_iul_toast(seconds) {
    if (iul_toast) {
        jQuery('#iul-toast-countdown').text(seconds);
    }
}

function hide_iul_toast() {
    if (iul_toast) {
        iul_toast.remove();
        iul_toast = null;
    }
}

function reset_iul_timer() {
    clearInterval(iul_countdown_interval);
    hide_iul_toast();
    idle_user_status_call('active');
    jQuery(document).idleTimer('destroy');
    jQuery(document).idleTimer(parseInt(iul.actions.timer) * 1000);
}

function idle_user_logout_callback(url, reload) {
    jQuery.ajax({
        type: 'POST',
        url: iul.ajaxurl,
        data: {
            action: 'logout_idle_user',
            nonce: iul.nonce
        },
        success: function (response) {
            if (response.success && response.data === true) {
                jQuery(window).unbind();
                if (reload) {
                    location.reload();
                } else {
                    window.location = url;
                }
            }
        },
        error: function (MLHttpRequest, textStatus, errorThrown) {
            console.log(errorThrown);
        }
    });
}

function idle_user_status_call(type) {
    jQuery.ajax({
        type: 'POST',
        url: iul.ajaxurl,
        data: {
            action: 'update_user_time',
            callType: type,
            nonce: iul.nonce
        },
        error: function (MLHttpRequest, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        success: function (response) {
            console.log(response);
        }
    });
    return null;
}

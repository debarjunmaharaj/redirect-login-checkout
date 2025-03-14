<?php
/**
 * Plugin Name: Redirect to Login for Checkout
 * Plugin URI: https://www.netfie.com/redirect-login-checkout
 * Description: Redirects non-logged-in users to a custom login page when they try to access the checkout page, with a popup reminder on the login page.
 * Version: 2.1
 * Author: Netfie
 * Author URI: https://www.netfie.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: redirect-login-checkout
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * Add settings menu to configure login page URL.
 */
add_action('admin_menu', 'rlc_add_settings_menu');

function rlc_add_settings_menu() {
    add_options_page(
        'Redirect Login Settings',
        'Redirect Login Settings',
        'manage_options',
        'redirect-login-checkout',
        'rlc_settings_page'
    );
}

function rlc_settings_page() {
    if (isset($_POST['rlc_save_settings'])) {
        $login_page_url = esc_url_raw($_POST['rlc_login_page_url']);
        update_option('rlc_login_page_url', $login_page_url);
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    $saved_url = esc_url(get_option('rlc_login_page_url', ''));
    ?>
    <div class="wrap">
        <h1>Redirect Login for Checkout</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="rlc_login_page_url">Custom Login Page URL</label></th>
                    <td>
                        <input type="url" id="rlc_login_page_url" name="rlc_login_page_url" value="<?php echo $saved_url; ?>" class="regular-text" required>
                        <p class="description">Enter the full URL of your login page. Example: <code>https://example.com/my-account/</code></p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

/**
 * Redirect non-logged-in users to the custom login page before checkout.
 */
add_action('template_redirect', 'rlc_redirect_before_checkout');

function rlc_redirect_before_checkout() {
    if (is_checkout() && !is_user_logged_in()) {
        $login_page_url = get_option('rlc_login_page_url', '');
        if ($login_page_url) {
            wp_redirect(add_query_arg('checkout_redirect', 'true', $login_page_url));
            exit;
        }
    }
}

/**
 * Show SweetAlert message on the login page after redirection.
 */
add_action('wp_footer', 'rlc_show_login_popup');

function rlc_show_login_popup() {
    $login_page_url = get_option('rlc_login_page_url', '');
    if ($login_page_url && strpos($_SERVER['REQUEST_URI'], untrailingslashit(parse_url($login_page_url, PHP_URL_PATH))) !== false && isset($_GET['checkout_redirect'])) {
        ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                swal({
                    title: "Please Log In",
                    text: "You need to log in to proceed with checkout.",
                    icon: "warning",
                    buttons: ["Cancel", "Log In"]
                });
            });
        </script>
        <?php
    }
}

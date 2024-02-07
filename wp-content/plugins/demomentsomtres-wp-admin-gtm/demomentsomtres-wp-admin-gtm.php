<?php
/*
  Plugin Name: DeMomentSomTres WP Admin GTM
  Plugin URI: http://demomentsomtres.com/english/wordpress-plugins/demomentsomtres-wp-admin-gtm/
  Description: Include GTM tracking code on WP Admin and WP Login
  Version: 1.0
  Author: Marc Queralt
  Author URI: http://demomentsomtres.com
 */

define('DMST_WPADMINGTM_TEXT_DOMAIN', 'DeMomentSomTres-WPAdmin-GTM');

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}
if (!function_exists('is_plugin_active'))
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
if ((!is_plugin_active('duracelltomi-google-tag-manager/duracelltomi-google-tag-manager-for-wordpress.php')) && (!is_plugin_active_for_network('duracelltomi-google-tag-manager/duracelltomi-google-tag-manager-for-wordpress.php'))):
    add_action('admin_notices', 'demomentsomtres_wpadmingtm_noTools');
else:
    $demomentsomtres_wpadmin_GTM = new DeMomentSomTresWPadminGTM;
endif;

function demomentsomtres_wpadmingtm_noTools() {
    ?>
    <div class="error">
        <p><?php _e('DeMomentSomTres WP Admin GTM plugin requires the DuracellTomi Google Tag Manager plugin.', DMST_WPADMINGTM_TEXT_DOMAIN); ?></p>
    </div>
    <?php
}

class DeMomentSomTresWPadminGTM {

    const TEXT_DOMAIN = DMST_WPADMINGTM_TEXT_DOMAIN;

    private $pluginURL;
    private $pluginPath;
    private $langDir;

    /**
     * @since 1.0
     */
    function __construct() {
        $this->pluginURL = plugin_dir_url(__FILE__);
        $this->pluginPath = plugin_dir_path(__FILE__);
        $this->langDir = dirname(plugin_basename(__FILE__)) . '/languages';

        add_action('plugins_loaded', array(&$this, 'plugin_init'));
        add_action('login_form', array(&$this, 'insert_GTM'));
        add_action('all_admin_notices', array(&$this, 'insert_GTM'));
    }

    /**
     * @since 1.0
     */
    function plugin_init() {
        load_plugin_textdomain(DMST_WPADMINGTM_TEXT_DOMAIN, false, $this->langDir);
    }

    /**
     * @since 1.0
     */
    function insert_GTM() {
        echo '<!--Start of DeMomentSomTres Google Tag Manager-->';
        if (!function_exists('gtm4wp_the_gtm_tag')):
            include_once $this->pluginPath . '../duracelltomi-google-tag-manager/public/frontend.php';
        endif;
        if (function_exists('gtm4wp_the_gtm_tag'))
            gtm4wp_the_gtm_tag();
        echo '<!--End of DeMomentSomTres Google Tag Manager-->';
    }

}

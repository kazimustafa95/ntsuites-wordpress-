<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/
namespace Smackcoders\CFCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class SmackCSVInstall {

	protected static $instance = null,$smack_instance,$tables_instance;

	/**
	 * SmackCSVInstall Constructor
	 */
	public function __construct() {
		$this->plugin = Plugin::getInstance();
		self::$tables_instance = new Tables();
	}

	/**
	 * SmackCSVInstall Instance
	 */
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	/**
	 * SmackCSVInstall constructor.
	 */
	public static function csvOptions(){
		$callbackUrl['callbackurl']=site_url().'/wp-admin/admin.php?action=csv_options&show=settings';
		echo json_encode($callbackUrl);
		wp_die();
	}

	public function doHooks(){
		add_action('wp_ajax_csv_options', array($this,'csvOptions'));
	}

	/** @var array DB updates that need to be run */
	private static $db_updates = array(
			'4.0.0' => 'updates/cf-uci-update-5.0.php',
			'4.1.0' => 'updates/cf-uci-update-5.0.php',
			'4.4.0' => 'updates/cf-uci-update-5.0.php',
			'4.5' => 'updates/cf-uci-update-5.0.php',
			'5.0' => 'updates/cf-uci-update-5.2.php',
			'5.1' => 'updates/cf-uci-update-5.2.php',
			'5.2' => 'updates/cf-uci-update-5.3.php',
			'5.3' => 'updates/cf-uci-update-5.3.php',
			'5.5' => 'updates/cf-uci-update-5.5.php'
			);

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
	}

	/**
	 * Check WPUltimateCSVImporterPro version.
	 */
	public static function check_version() {
		if ( get_option( 'ULTIMATE_CSV_IMP_VERSION' ) != SmackUCI()->version )  {
			self::install();
			do_action( 'sm_uci_pro_updated' );
		}
	}

	/**
	 * Install actions when a update button is clicked.
	 */
	public static function install_actions() {
		if ( ! empty( $_GET['do_update_sm_uci_pro'] ) ) {
			self::update();
		}
	}

	/**
	 * Show notice stating update was successful.
	 */
	public static function updated_notice() {
		?>
			<div class='notice updated uci-message wc-connect is-dismissible'>
			<p><?php esc_html__( 'Importer Custom fields PRO data update complete. Thank you for updating to the latest version!', 'wp-importer-customfields-pro' ); ?></p>
			</div>
			<?php
	}

	/**
	 * Install WUCI.
	 */
	public  function install() {
		$current_uci_version    = get_option( 'ULTIMATE_CSV_IMP_VERSION', null );
		self::$tables_instance->create_tables(); 
		// No versions? This is a new install :)
		if ( is_null( $current_uci_version )) {
			self::create_options();         // Create option data on the initial stage
			self::CustomField_controls();
		} else {

		}
		self::update_uci_version();
		flush_rewrite_rules();
		do_action( 'sm_uci_installed' );
	}

	/**
	 * Update UCI version to current.
	 */
	private static function update_uci_version() {
		$version = '2.0';
		delete_option( 'ULTIMATE_CSV_IMP_VERSION' );
		add_option( 'ULTIMATE_CSV_IMP_VERSION', $version );
	}

	/**
	 * @param null $version
	 * Update DB version to current.
	 */
	private static function update_db_version( $version = null ) {
		delete_option( 'sm_uci_db_version' );
		add_option( 'sm_uci_db_version', is_null( $version ) ? SmackUCI()->version : $version );
	}

	/**
	 * Handle updates.
	 */
	private static function update() {
		$current_db_version = get_option( 'ULTIMATE_CSV_IMP_VERSION' );
		foreach ( self::$db_updates as $version => $updater ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				//include_once ( $updater );
				self::update_db_version( $version );
			}
		}

		self::update_db_version();
	}

	/**
	 * Add more cron schedules.
	 * @param  array $schedules
	 * @return array
	 */
	public static function cron_schedules( $schedules ) {
		return array(
				'wp_ultimate_csv_importer_scheduled_csv_data' => array(
					'interval' => 5, // seconds
					'display' => __('Check scheduled events on every second', 'wp-importer-customfields-pro')
					),
				'wp_ultimate_csv_importer_scheduled_images' => array(
					'interval' => 10, // seconds
					'display' => __('Schedule images on every second', 'wp-importer-customfields-pro')
					),
				'wp_ultimate_csv_importer_scheduled_emails' => array(
					'interval' => 5, // seconds
					'display' => __('Schedule emails on every second', 'wp-importer-customfields-pro')
					),
				'wp_ultimate_csv_importer_replace_inline_images' => array(
					'interval' => 5, // seconds
					'display' => __('Replace all inline images from post content', 'wp-importer-customfields-pro')
					)
					);
	}

	public static function curlArgs($response) {
		$response['sslverify'] = false;
		return $response;
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	public static function create_options() {

		// We assign the default option data for the fresh instalization
		$settings = array('debug_mode' => 'off',
				'send_log_email' => 'on',
				'drop_table' => 'off',
				'author_editor_access' => 'off',
				'woocomattr' => 'off',
				'unmatchedrow' => 'off'
				);

		add_option('sm_uci_pro_settings', $settings);

	}

	/**
	 * Set Custom Field Controls
	 */
	private static function CustomField_controls() {
		$acf_controls = array(
				'Basic' => array('Text','Text Area','Number','Email','Url','Password'),
				'Content' => array('Wysiwyg Editor','oEmbed','Image','File','Gallery'),
				'Choice' => array('Select','Checkbox','Radio Button','True/False'),
				'Relational' => array('Post Object','Page Link','Relationship','Taxonomy','User'),
				'jQuery' => array('Google Map','Date Picker','Color picker'),
				'Layout' => array('Message','Tab','Repeater','Flexible Content')
				);
		$pods_controls = array(
				'Text' => array('Plain Text','Website','Phone','Email','Password'),
				'Paragraph' => array('Plain Paragraph Text','WYSIWYG (Visual Editor)','Code (Syntax Highlighting)'),
				'Date/Time' => array('Date/Time','Date','Time'),
				'Number' => array('Plain Number','Currency'),
				'Relationships/Media' => array('File/Image/Video','Relationship'),
				'Other' => array('Yes/No','Color Picker')
				);
		$types_controls = array(
				'Text'=> array('Textfield','Textarea','Numeric','Phone','Email','Url'),
				'Content' => array('Wysiwyg','Embed','Image','File','Video','Skype'),
				'Choice' => array('Select','Checkbox','Checkboxes','Radio'),
				'jQuery' => array('Colorpicker','Date')
				);
		self::insert_CF_controls($acf_controls,'acf-field-type');
		self::insert_CF_controls($pods_controls,'pods-field-type');
		self::insert_CF_controls($types_controls,'types-field-type');
	}

	/**
	 * Insert Custom Field Controls
	 *
	 * @param $cf_controls
	 * @param $cf_type
	 */
	private static function insert_CF_controls($cf_controls,$cf_type) {
		global $wpdb;
		foreach($cf_controls as $cf_group => $cf_fields) {
			$cf_fields = serialize($cf_fields);
			$cf_insert = "insert into ultimate_cfimporter_pro_smack_field_types(choices,fieldType,groupType)select * from (select '$cf_fields','$cf_group','$cf_type')as tmp where not exists(select groupType from ultimate_cfimporter_pro_smack_field_types where groupType = '$cf_type' and fieldType = '$cf_group')";
			$wpdb->query($cf_insert);
		}
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param       mixed $links Plugin Row Meta
	 * @param       mixed $file  Plugin Base file
	 * @return      array
	 */
	public static function plugin_row_meta( $links, $file ) {
			$row_meta = array(
				'settings' => '<a href="' . esc_url( apply_filters( 'WP Importer Custom fields Pro_url', admin_url() . 'admin.php?page=cf-uci-settings' ) ) . '" title="' . esc_attr( __( 'Visit Plugin Settings', 'wp-importer-customfields-pro' ) ) . '" target="_blank">' . __( 'Settings', 'wp-importer-customfields-pro' ) . '</a>',
				'docs'     => '<a href="' . esc_url( apply_filters( 'sm_uci_docs_url', 'https://www.smackcoders.com/wp-ultimate-csv-importer-pro.html' ) ) . '" title="' . esc_attr( __( 'View WP Importer Custom fields PRO Documentation', 'wp-importer-customfields-pro' ) ) . '" target="_blank">' . __( 'Docs', 'wp-importer-customfields-pro' ) . '</a>',
				'videos'   => '<a href="' . esc_url( apply_filters( 'sm_uci_videos_url', 'https://www.youtube.com/watch?v=HHPpS0uZzeU&list=PL2k3Ck1bFtbTT-5Tz2PLkoAUJ1fRdwUSj&feature=youtu.be' ) ) . '" title="' . esc_attr( __( 'View Videos for WP Importer Custom fields PRO', 'wp-importer-customfields-pro' ) ) . '" target="_blank">' . __( 'Videos', 'wp-importer-customfields-pro' ) . '</a>',
				'support'  => '<a href="' . esc_url( apply_filters( 'sm_uci_support_url', admin_url() . 'admin.php?page=cf-uci-support' ) ) . '" title="' . esc_attr( __( 'Contact Support', 'wp-importer-customfields-pro' ) ) . '" target="_blank">' . __( 'Support', 'wp-importer-customfields-pro' ) . '</a>',
			);

			return array_merge( $row_meta, $links );
		}

	public static function after_plugin_row_meta() {
		$response = wp_safe_remote_get('https://www.smackcoders.com/wp-versions/wp-importer-custom-fields-basic-pro.json');
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$response = json_decode($response['body']);
		$current_plugin_version = '2.0.5';
		if($current_plugin_version < $response->version[0]) {
			echo '<tr class="active"><td colspan="3">';
			echo '<div class="update-message notice inline notice-warning notice-alt"><p>There is a new version of WP Importer Custom fields PRO <b>[ version '. $response->version[0] .' ]</b> available. <a href="https://smackcoders.com/my-account.html" class="update-link" aria-label="Upgrade WP Importer Custom fields PRO now"> Upgrade now</a>.</p></div>';
			echo '</td></tr>';
		}
	}

	public static function important_cron_notice() {
		$get_notice = get_option('smack_uci_cron_notice');
		if($get_notice != 'off' && isset($_REQUEST['page']) && sanitize_text_field($_REQUEST['page']) == 'cf-uci-import') {
			?>
			<div class="notice notice-error wc-connect is-dismissible" onclick="dismiss_notices('cron_notice');" >
			<p style="margin-top: 10px">
			<strong><?php echo esc_html__( 'Notice:', 'wp-importer-customfields-pro' ); ?> </strong> <?php echo esc_html__( 'To populate Featured images, Please make sure that CRON is enabled in your server. ', 'wp-importer-customfields-pro' ); ?></p>
			</div>
			<?php
			if(function_exists( 'curl_version' ) == null || function_exists( 'curl_version' ) == '' && isset($_REQUEST['page']) && sanitize_text_field($_REQUEST['page']) == 'cf-uci-import') { ?>
				<div class="notice notice-error">
					<p style="margin-top: 10px;">
						<strong><?php echo esc_html__( 'Notice:', 'wp-importer-customfields-pro' ); ?> </strong> <?php echo esc_html__( 'Please install CURL & enable it in your server. ', 'wp-importer-customfields-pro' ); ?>
					</p>
				</div>
			<?php }
		}
	}

	public static function wp_ultimate_csv_importer_notice() {
		$get_notice = get_option('smack_uci_upgrade_notice');
		$smack_uci_pages = array('cf-uci-import', 'cf-uci-dashboard', 'cf-uci-managers', 'sm-uci-export', 'cf-uci-settings', 'cf-uci-support');
		if($get_notice != 'off' && isset($_REQUEST['page']) && in_array(sanitize_text_field($_REQUEST['page']), $smack_uci_pages)) {
			?>
			<div class='notice updated uci-message wc-connect is-dismissible' onclick="dismiss_notices('upgrade_notice');">
				<?php
				if ( get_option( 'ULTIMATE_CSV_IMP_VERSION' ) == 5.0 ) {
					?>
					<p><?php echo esc_html__( 'Importer Custom fields PRO data update complete. Thank you for updating to the latest version!', 'wp_ultimate_csv_importer_pro' ); ?></p>
				<?php } ?>
				<p><?php echo esc_html__("If you love WP Ultimate CSV Importer show us you care with a 5-star review on","wp-importer-customfields-pro");?> <a href='https://wordpress.org/support/plugin/wp-ultimate-csv-importer/reviews/?rate=5#new-post' target='_blank'><?php echo esc_html__("wordpress.org!","wp-importer-customfields-pro");?></a>
				</p></div>
			<?php
		}
	}
}

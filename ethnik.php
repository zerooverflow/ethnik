<?php
/**
 * Plugin Name: Ethnik
 * Plugin URI: http://www.aimconsulting.it
 * Description: Gestire aree e visibilità degli articoli creando gruppi di utenti.
 * Version: 1.0
 * Author: Simone Buono	
 * Author URI: http://it.linkedin.com/in/simonebuono/
 * Requires at least: 3.8
 * Tested up to: 4.0
 *
 * Text Domain: ethnik
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define ('PLUGIN_DIRPATH', plugin_dir_path( __FILE__ ));
define ('PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! class_exists( 'Ethnik' ) ) :

final class Ethnik {
	
	public $version = '1.0';
	
	public function __construct()
	{
		add_action('admin_init', array(&$this, 'admin_init'));
		add_action('admin_menu', array(&$this, 'add_menu'));
		
		add_action( 'admin_enqueue_scripts', array(&$this, 'css_js') );
		
	}
	
	public static function activate(){
		global $wpdb;
		
		$table_groups = $wpdb->prefix . 'ethnik_groups';
		
		$table_relationships =  $wpdb->prefix . 'ethnik_relationships';
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $table_groups (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name tinytext NOT NULL,
		description text NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;
		CREATE TABLE $table_relationships (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		groupid mediumint(9) NOT NULL,
		userid mediumint(9) NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;
		";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
	}
	
	public static function deactivate(){
				
	}
	
	public function admin_init()
	{
		$this->init_settings();
		
		$this->includes();
		
		
	}
	
	public function init_settings()
	{
		register_setting('wp_ethnik-group', 'ethnik_activate');
		
	}
	
	public function includes(){
		include(sprintf("%s/includes/ethnik-db.php", dirname(__FILE__)));
		include(sprintf("%s/includes/ethnik-functions.php", dirname(__FILE__)));
	}
	
	public function css_js(){
		wp_register_style( 'ethnik_wp_admin_css', PLUGIN_URL. 'css/admin-style.css', false, '1.0.0' );
		wp_enqueue_style( 'ethnik_wp_admin_css' );
		
		wp_enqueue_script('ethnik_admin_script',PLUGIN_URL.'js/admin-script.js', array( 'jquery' ) );
		
		
	}
	
	public function add_menu()
	{
		add_menu_page('Ethnik Settings', 'Ethnik', 'manage_options', 'wp_ethnik', array(&$this, 'ethnik_settings_page'),'dashicons-groups');
		add_submenu_page( 'wp_ethnik', 'Gestisci gruppi', 'Gestisci gruppi', 'manage_options', 'ethnik-gruppi', array(&$this, 'ethnik_gruppi_page') );
	} 
	

	public function ethnik_settings_page()
	{
		if(!current_user_can('manage_options'))
		{
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
	
		include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
	} 
	
	public function ethnik_gruppi_page(){
		
		if(!current_user_can('manage_options'))
		{
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		include(sprintf("%s/templates/groups.php", dirname(__FILE__)));
	}
	
}

endif;

if(class_exists('Ethnik'))
{
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('Ethnik', 'activate'));
	register_deactivation_hook(__FILE__, array('Ethnik', 'deactivate'));

	// instantiate the plugin class
	$wp_ethnik = new Ethnik();
	
	if( isset( $wp_ethnik ) )
	{
		// Add the settings link to the plugins page
		function ethnik_settings_link($links)
		{
			$settings_link = '<a href="options-general.php?page=wp_ethnik">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}
	
		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_$plugin", 'ethnik_settings_link');
		
		
		
		
	}
	
}
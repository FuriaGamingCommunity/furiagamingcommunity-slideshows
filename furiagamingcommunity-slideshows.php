<?php
/**
 * The Furia Gaming Community - Slideshows Plugin.
 *
 * Furia Gaming Community - Slideshows is a plugin made for http://furiaguild.com that adds sliding article showcase areas.
 *
 * @package  FuriaGamingCommunity
 * @subpackage FuriaGamingCommunity_Slideshows
 * @author Xavier Giménez <xavier.gimenez.segovia@gmail.com>
 * @license GPL-2.0+
 **/

/**
 * Plugin Name:     Furia Gaming Community - Slideshows
 * Plugin URI:      https://github.com/nottu2584/furiagamingcommunity-slideshow
 * Description:     Sets a new post type named slides and adds a custom widget to display them into slideshows.
 * Author:          Xavier Giménez
 * Author URI:      https://es.linkedin.com/pub/javier-gimenez-segovia/
 * Author Email:    xavier.gimenez.segovia@gmail.com
 * Version:         1.2.0
 * Text Domain:     furiagamingcommunity_slideshows
 * License:         GPLv2 or later (LICENSE)
**/

// Exit if accessed directly
defined('ABSPATH') || exit;

if(!class_exists('FuriaGamingCommunity_Slideshows')) :

/**
 * Inits the plugin dependencies.
 *
 * @author Xavier Giménez
 * @version 1.2.0
 */
class FuriaGamingCommunity_Slideshows {

	public static function instance(){

		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been run previously
		if(null === $instance){

			// Setup plugin object.
			$instance = new FuriaGamingCommunity_Slideshows;

			// Setup plugin dependencies
			$instance->constants();
			$instance->setup_globals();
			$instance->includes();
			$instance->classes();
			$instance->actions();
		}

		// Always return the instance
		return $instance;
	}

	/**
	 * A dummy constructor to prevent FuriaGamingCommunity_Slideshows from being loaded more than once.
	 * @since 1.0.0
	 */
	private function __construct(){

		// Do nothing.
	}

	/**
	 * Setup actions.
	 *
	 * @since 1.1.0
	 */
	private function actions(){
		// Add the widget.
		add_action('widgets_init', 			array($this, 'init_slide_widget'		) );
		// Enqueue related scritps.
		add_action('wp_enqueue_scripts', 	array($this, 'enqueue_slide_scripts'	) );
		add_action('wp_enqueue_scripts', 	array($this, 'enqueue_slide_styles'		) );
	}

	/**
	 * Setup classes.
	 *
	 * @since 1.0.0
	 */
	private function classes(){

		$this->slide = new Slides();
	}

	/**
	 * Bootstrap constants.
	 *
	 * @since 1.0.0
	 *
	 * @uses plugin_dir_path()
	 * @uses plugin_dir_url()
	 */
	private function constants(){

		// Path
		if(! defined('FGC_S_PLUGIN_DIR'))
			define('FGC_S_PLUGIN_DIR', plugin_dir_path(__FILE__));
		// URL
		if(! defined('FGC_S_PLUGIN_URL'))
			define('FGC_S_PLUGIN_URL', plugin_dir_url(__FILE__));
	}

	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 */
	private function includes(){

		require($this->plugin_dir . 'includes/functions.php');
		//require($this->plugin_dir . 'includes/messages.php');
		require($this->plugin_dir . 'includes/classes/class-slides.php');
		require($this->plugin_dir . 'includes/classes/class-slides-wp-widget.php');
	}

	/**
	 * Declare class constants.
	 *
	 * @since 1.0.0
	 */
	private function setup_globals(){

		$this->file           = constant('FGC_S_PLUGIN_DIR') . __FILE__;
		$this->basename       = basename(constant('FGC_S_PLUGIN_DIR')) . __FILE__;

		$this->plugin_dir     = trailingslashit(constant('FGC_S_PLUGIN_DIR'));
		$this->plugin_url     = trailingslashit(constant('FGC_S_PLUGIN_URL'));
	}

	/**
	 * Enqueue custom scripts.
	 * @since 1.2.0
	 */
	public function enqueue_slide_scripts() {
		wp_register_script('slide_script', FGC_S_PLUGIN_URL . 'js/slides.js', array('jquery'));
		wp_enqueue_script('slide_script');
	}

	public function enqueue_slide_styles() {
		wp_register_style( 'slide_style', FGC_S_PLUGIN_URL . 'css/slides.css', false, '1.0.0' );
		wp_enqueue_style( 'slide_style' );
	}

	/**
	 * Register the slideshow widget.
	 * @since 1.2.0
	 */
	public function init_slide_widget() {
		// Register widget.
		register_widget( 'Slides_WP_Widget' );
	}

} // class FuriaGamingCommunity_Slideshows


/**
 * Launch a single instance of the plugin
 * @since 1.0.0
 *
 * @return FuriaGamingCommunity_Slideshows The plugin instance
 */
function furiagamingcommunity_slideshows(){
	return FuriaGamingCommunity_Slideshows::instance();
}
add_action('plugins_loaded', 'furiagamingcommunity_slideshows');

/**
 * Register the text domain
 * @since 1.0.0
 */
function furiagamingcommunity_slideshows_load_textdomain(){
	load_plugin_textdomain('furiagamingcommunity_slideshows', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'furiagamingcommunity_slideshows_load_textdomain');

endif;
?>

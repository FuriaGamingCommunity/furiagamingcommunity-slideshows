<?php
/**
 * Plugin Slide Class.
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

if(!class_exists('Slides')) :

/**
 * Registers the "Slide" custom post type.
 * Slides are used in slideshows to showcase articles.
 *
 * @author Xavier Giménez
 * @version 1.1.1
 */
class Slides {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;
	/**
	 * Register the custom post and taxonomy with WordPress on init
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add universal actions
		add_action( 'init', array( $this , 'register_slides' ) );
		add_action( 'init', array( $this , 'register_slideshows' ) );
		add_action( 'init', array( $this , 'register_slide_size' ) );

		// Admin-only methods
		if ( is_admin() ) {
			// Settings actions
			add_action( 'admin_menu'				, array( $this, 'add_plugin_page' ) );
			add_action( 'admin_init'				, array( $this, 'page_init' ) );

			// Slide actions
			add_action( 'do_meta_boxes'				, array( $this , 'slide_image_box' 			) );
			add_action( 'admin_menu'				, array( $this , 'change_slide_link' 		) );
			add_action( 'save_post'					, array( $this , 'save_meta' 				) );
			add_action( 'add_attachment'			, array( $this , 'save_meta' 				) );
			add_action( 'edit_attachment'			, array( $this , 'save_meta' 				) );
			add_action( 'manage_posts_custom_column', array( $this , 'slides_custom_columns'	) );

			// Admin filters
			add_filter( 'post_updated_messages'		, array( $this , 'slide_updated_messages' 	) );
			add_filter( 'manage_edit-slide_columns'	, array( $this , 'slides_edit_columns'		) );
		}
	}

	/**
	 * Add a featured image size for the slides
	 * @since 1.0.0
	 */
	public function register_slide_size() {

		if ( !isset($this->options['width']) )
			$this->options['width'] = 1950;
		if ( !isset($this->options['height']) )
			$this->options['height'] = 1080;
		// Register the image size
		add_image_size( 'featured-slide' , $this->options['width'] , $this->options['height'] , true );
	}

	/**
	 * Register a custom post type for Slides
	 * @version 1.0.0
	 */
	public function register_slides() {
		// Labels for the backend slide publisher
		$slide_labels = array(
			'name'					=> __('Slides', 'furiagamingcommunity_slideshows'),
			'singular_name'			=> __('Slide', 'furiagamingcommunity_slideshows'),
			'add_new'				=> __('New Slide', 'furiagamingcommunity_slideshows'),
			'add_new_item'			=> __('Add New Slide', 'furiagamingcommunity_slideshows'),
			'edit_item'				=> __('Edit Slide', 'furiagamingcommunity_slideshows'),
			'new_item'				=> __('New Slide', 'furiagamingcommunity_slideshows'),
			'view_item'				=> __('View Slide', 'furiagamingcommunity_slideshows'),
			'search_items'			=> __('Search Slides', 'furiagamingcommunity_slideshows'),
			'not_found'				=> __('No slides found', 'furiagamingcommunity_slideshows'),
			'not_found_in_trash'	=> __('No slides found in Trash', 'furiagamingcommunity_slideshows'),
			'parent_item_colon'		=> '',
			'menu_name'				=> __('Slides', 'furiagamingcommunity_slideshows'),
			);

		$slide_capabilities = array(
			'edit_post'				=> 'edit_post',
			'edit_posts'			=> 'edit_posts',
			'edit_others_posts'		=> 'edit_others_posts',
			'publish_posts'			=> 'publish_posts',
			'read_post'				=> 'read_post',
			'read_private_posts'	=> 'read_private_posts',
			'delete_post'			=> 'delete_post'
			);
		// Construct the arguments for our custom slide post type
		$slide_args = array(
			'labels'				=> $slide_labels,
			'description'			=> __('Slides are a custom post type used for generating slideshows.', 'furiagamingcommunity_slideshows'),
			'public'				=> true,
			'publicly_queryable'	=> false,
			'exclude_from_search'	=> true,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> false,
			'menu_icon'				=> 'dashicons-images-alt',
			'menu_position'			=> 7,
			'capabilities'			=> $slide_capabilities,
			'map_meta_cap'			=> true,
			'hierarchical'			=> false,
			'supports'				=> array( 'title', 'editor', 'thumbnail' ),
			'taxonomies'			=> array( 'slideshow' ),
			'has_archive'			=> false,
			'rewrite'				=> false,
			'query_var'				=> true,
			'can_export'			=> true,
			);

		// Register the Slide post type!
		register_post_type( 'slide', $slide_args );
	}

	/**
	 * Register a custom post taxonomy for Slideshows
	 * @since 1.0.0
	 */
	public function register_slideshows() {

		$slideshow_tax_labels = array(
			'name'							=> __('Slideshows', 'furiagamingcommunity_slideshows'),
			'singular_name'					=> __('Slideshow', 'furiagamingcommunity_slideshows'),
			'search_items'					=> __('Search Slideshows', 'furiagamingcommunity_slideshows'),
			'popular_items'					=> __('Popular Slideshows', 'furiagamingcommunity_slideshows'),
			'all_items'						=> __('All Slideshows', 'furiagamingcommunity_slideshows'),
			'edit_item'						=> __('Edit Slideshow', 'furiagamingcommunity_slideshows'),
			'update_item'					=> __('Update Slideshow', 'furiagamingcommunity_slideshows'),
			'add_new_item'					=> __('Add New Slideshow', 'furiagamingcommunity_slideshows'),
			'new_item_name'					=> __('New Slideshow Name', 'furiagamingcommunity_slideshows'),
			'menu_name'						=> __('Slideshows', 'furiagamingcommunity_slideshows'),
			'separate_items_with_commas'	=> __('Separate slideshows with commas', 'furiagamingcommunity_slideshows'),
			'choose_from_most_used'			=> __('Choose from the most used slideshows', 'furiagamingcommunity_slideshows'),
			);
		$slideshow_tax_caps = array(
			'manage_terms'	=> 'manage_categories',
			'edit_terms'	=> 'manage_categories',
			'delete_terms'	=> 'manage_categories',
			'assign_terms'	=> 'edit_posts'
			);
		$slideshow_tax_args = array(
			'labels'				=> $slideshow_tax_labels,
			'public'				=> true,
			'show_ui'				=> true,
			'show_in_nav_menus'		=> false,
			'show_tagcloud'			=> false,
			'hierarchical'			=> true,
			'rewrite'				=> array( 'slug' => 'slideshow' ),
			'capabilities'    	  	=> $slideshow_tax_caps,
			);
		// Register the Slideshow post taxonomy
		register_taxonomy( 'slideshow', 'slide', $slideshow_tax_args );
	}

	/**
	 * Place the "featured image" box in the main listing, since it's the key element here.
	 * @since 1.0.0
	 */
	public function slide_image_box() {
		$slide_image_title = __('Set the slide image', 'furiagamingcommunity_slideshows') . '(' . $this->options['width'] . 'x' . $this->options['height'] . ')';
		remove_meta_box( 'postimagediv', 'slide', 'side' );
		add_meta_box( 'postimagediv', $slide_image_title, 'post_thumbnail_meta_box' , 'slide', 'normal', 'high' );
	}

	/**
	 * Get rid of the "slug" box, show our permalink box instead.
	 * @since 1.0.0
	 */
	public function change_slide_link() {
		remove_meta_box( 'slugdiv', 'slide', 'core' );
		add_meta_box( 'slide-settings', __('Slide Settings', 'furiagamingcommunity_slideshows'), array( $this , 'settings_box' ) , 'slide', 'normal', 'high' );
	}

	/**
	 * Display inputs for our custom slide meta fields
	 * @since 1.0.0
	 */
	public function settings_box( $object , $box ) {
		wp_nonce_field( basename( __FILE__ ), 'slideshow-settings-box' );
		?>

		<p>
			<label for="slide-tabtitle"><?php _e('Slide Tab Text', 'furiagamingcommunity_slideshows'); ?></label><br />
			<input type="text" name="slide-tabtitle" id="slide-tabtitle" value="<?php echo esc_attr( get_post_meta( $object->ID, 'TabTitle', true ) ); ?>" size="55" tabindex="10"/>
		</p>

		<p>
			<label for="slide-permalink"><?php _e('Set Slide Permalink', 'furiagamingcommunity_slideshows'); ?></label><br />
			<input type="text" name="slide-permalink" id="slide-permalink" value="<?php echo esc_attr( get_post_meta( $object->ID, 'Permalink', true ) ); ?>" size="55" tabindex="10"/>
		</p>

		<p>
			<label for="slide-textposition"><?php _e('Slide Text Position', 'furiagamingcommunity_slideshows'); ?></label><br />
			<select name="slide-textposition" id="slide-textposition" tabindex="10">
				<option value="left" default <?php selected( get_post_meta( $object->ID, 'TextPosition', true ), 'left' ); ?>><?php _e('Left', 'furiagamingcommunity_slideshows'); ?></option>
				<option value="right" <?php selected( get_post_meta( $object->ID, 'TextPosition', true ), 'half-width' ); ?>><?php _e('Right', 'furiagamingcommunity_slideshows'); ?></option>
			</select>
		</p>

		<p>
			<label for="slide-width"><?php _e('Slide Width', 'furiagamingcommunity_slideshows'); ?></label><br />
			<select name="slide-width" id="slide-width" tabindex="10">
				<option value="full-width" default <?php selected( get_post_meta( $object->ID, 'Width', true ), 'full-width' ); ?>><?php _e('Full width', 'furiagamingcommunity_slideshows'); ?></option>
				<option value="half-width" <?php selected( get_post_meta( $object->ID, 'Width', true ), 'half-width' ); ?>><?php _e('Half width', 'furiagamingcommunity_slideshows'); ?></option>
			</select>
		</p>

		<?php
	}

	/**
	 * Save the slide meta fields
	 * @version 1.0.0
	 */
	public function save_meta( $post_id ) {

		// Verify the nonce before proceeding.
		if ( !isset( $_POST['slideshow-settings-box'] ) || !wp_verify_nonce( $_POST['slideshow-settings-box'], basename( __FILE__ ) ) )
			return $post_id;

		// Assign names for the slide metadata
		$meta = array(
			'TabTitle'     => 	$_POST['slide-tabtitle'],
			'Permalink'    => 	$_POST['slide-permalink'],
			'TextPosition' => 	$_POST['slide-textposition'],
			'Width'        => 	$_POST['slide-width']
			);

		foreach ( $meta as $meta_key => $new_meta_value ) {
			// Get the meta value of the custom field key.
			$meta_value = get_post_meta( $post_id, $meta_key, true );
			// If there is no new meta value but an old value exists, delete it.
			if ( current_user_can( 'delete_post_meta', $post_id, $meta_key ) && '' == $new_meta_value && $meta_value )
				delete_post_meta( $post_id, $meta_key, $meta_value );
			// If a new meta value was added and there was no previous value, add it.
			elseif ( current_user_can( 'add_post_meta', $post_id, $meta_key ) && $new_meta_value && '' == $meta_value )
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );
			// If the new meta value does not match the old value, update it.
			elseif ( current_user_can( 'edit_post_meta', $post_id, $meta_key ) && $new_meta_value && $new_meta_value != $meta_value )
				update_post_meta( $post_id, $meta_key, $new_meta_value );
		}
	}

	/**
	 * Customize backend messages when a slide is updated
	 * @since 1.0.0
	 */
	public function slide_updated_messages( $slide_messages ) {
		global $post, $post_ID;

		// Set some simple messages for editing slides, no post previews needed.
		$slide_messages['slide'] = array(
			0	=> '',
			1	=> __('Slide updated.', 'furiagamingcommunity_slideshows'),
			2	=> __('Custom field updated.', 'furiagamingcommunity_slideshows'),
			2	=> __('Custom field deleted.', 'furiagamingcommunity_slideshows'),
			4	=> __('Slide updated.', 'furiagamingcommunity_slideshows'),
			5	=> isset($_GET['revision']) ? sprintf( __('Slide restored to revision from %s', 'furiagamingcommunity_slideshows'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6	=> __('Slide published.', 'furiagamingcommunity_slideshows'),
			7	=> __('Slide saved.', 'furiagamingcommunity_slideshows'),
			8	=> __('Slide submitted.', 'furiagamingcommunity_slideshows'),
			9	=> __('Slide scheduled for', 'furiagamingcommunity_slideshows') . sprintf( ': <strong>%1$s</strong>.' , strtotime( $post->post_date ) ),
			10	=> __('Slide draft updated.', 'furiagamingcommunity_slideshows'),
			);
		return $slide_messages;
	}

	/**
	 * Adds the slide featured image and link to the slides page
	 * @since 1.0.0
	 */
	public function slides_edit_columns( $columns ) {
		$columns = array(
			'cb'			=> '<input type="checkbox" />',
			'slide'			=> __('Slide Image', 'furiagamingcommunity_slideshows'),
			'title'			=> __('Slide Title', 'furiagamingcommunity_slideshows'),
			'slideshow'		=> __('Slideshow', 'furiagamingcommunity_slideshows'),
			'slide-width'	=> __('Slide Width', 'furiagamingcommunity_slideshows'),
			'slide-link'	=> __('Slide Link', 'furiagamingcommunity_slideshows'),
			'date'			=> __('Date', 'furiagamingcommunity_slideshows'),
			);
		return $columns;
	}

	/**
	 * Adds content to the custom column format
	 * @since 1.0.0
	 */
	public function slides_custom_columns( $columns ) {
		global $post;
		switch ( $columns ) {
			case 'slide' :
			echo get_the_post_thumbnail( $post->ID , 'medium');
			break;

			case 'slideshow' :
			echo get_the_term_list( $post->ID , 'slideshow' );
			break;

			case 'slide-width' :
			echo get_post_meta($post->ID, "Width", $single = true);
			break;

			case 'slide-link' :
			if ( get_post_meta($post->ID, "Permalink", $single = true) != "" ) {
				echo "<a href='" . get_post_meta($post->ID, "Permalink", $single = true) . "'>" . get_post_meta($post->ID, "Permalink", $single = true) . "</a>";
			} else {
				_e('No Link', 'furiagamingcommunity_slideshows');
			}
			break;
		}
	}

	/**
	 * Add options page
	 * @since 1.1.0
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			__('Slides Settings', 'furiagamingcommunity_slideshows'),
			__('Slides', 'furiagamingcommunity_slideshows'),
			'manage_options',
			'slides-admin',
			array( $this, 'create_admin_page' )
			);
	}

	/**
	 * Options page callback
	 * @since 1.1.0
	 */
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( 'slides_option' );
		?>
		<div class="wrap">
			<h2><?php _e('Slides Settings', 'furiagamingcommunity_slideshows'); ?></h2>
			<p><?php _e('Set the defaults for each slide and slideshow you create. Please bear in mind that this settings would be overwritten if you customize any slideshow widget parameters individually.', 'furiagamingcommunity_slideshows'); ?></p>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'slides_group' );
				do_settings_sections( 'slides-admin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 * @since 1.1.0
	 */
	public function page_init() {
		register_setting(
			'slides_group', // Option group
			'slides_option', // Option name
			array( $this, 'sanitize' ) // Sanitize
			);
		add_settings_section(
			'slides_section_size', // ID
			__('Size Settings', 'furiagamingcommunity_slideshows'), // Title
			array( $this, 'print_section_info' ), // Callback
			'slides-admin' // Page
			);
		add_settings_field(
			'width', // ID
			__('Width', 'furiagamingcommunity_slideshows'), // Title
			array( $this, 'width_callback' ), // Callback
			'slides-admin', // Page
			'slides_section_size' // Section
			);
		add_settings_field(
			'height',
			__('Height', 'furiagamingcommunity_slideshows'), // Title
			array( $this, 'height_callback' ),
			'slides-admin',
			'slides_section_size'
			);
	}

	/**
	 * Sanitize each setting field as needed
	 * @since 1.1.0
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if( isset( $input['width'] ) )
			$new_input['width'] = absint( $input['width'] );
		if( isset( $input['height'] ) )
			$new_input['height'] = absint( $input['height'] );
		return $new_input;
	}

	/**
	 * Print the Section text
	 * @since 1.1.0
	 */
	public function print_section_info() {
		_e('Set the default size for each slide');
	}

	/**
	 * Get the settings option array and print one of its values
	 * @since 1.1.0
	 */
	public function width_callback() {
		printf(
			'<input type="number" id="width" min="0" name="slides_option[width]" value="%s" />',
			isset( $this->options['width'] ) ? esc_attr( $this->options['width']) : ''
			);
	}

	/**
	 * Get the settings option array and print one of its values
	 * @since 1.1.0
	 */
	public function height_callback() {
		printf(
			'<input type="number" id="height" min="0" name="slides_option[height]" value="%s" />',
			isset( $this->options['height'] ) ? esc_attr( $this->options['height']) : ''
			);
	}
} // class Slides

endif;
?>

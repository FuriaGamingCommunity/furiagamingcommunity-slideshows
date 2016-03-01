<?php
/**
 * Plugin Name: Furia Gaming Community - Slideshow
 * Plugin URI: http://furiaguild.com
 * Description: Sets a new post type named slides and adds a custom widget to display them into slideshows.
 * Author: Xavier Giménez Segovia
 * Version: 1.1
 * Author URI: https://es.linkedin.com/pub/javier-gimenez-segovia/
 * Text Domain: furiagamingcommunity_slideshow
**/

defined( 'ABSPATH' ) or die( __( 'No script kiddies please!', 'furiagamingcommunity_slideshow' ) );

/**
 * Registers the "Slide" custom post type.
 * Slides are used with the taxonomy "Slideshow" to display images for Flexslider.
 *
 * @author Xavier Giménez Segovia
 * @version 1.1.0
 */
class FuriaGamingCommunity_Slides {

	// Register slide dimensions
	public $height 	= 450;
	public $width 	= 900;

	/**
	 * Register the custom post and taxonomy with WordPress on init
	 * @since 0.1
	 */
	function __construct() {

		// Add universal actions
		add_action( 'init', array( $this , 'register_slides' ) );
		add_action( 'init', array( $this , 'register_slideshows' ) );
		add_action( 'init', array( $this , 'register_slide_size' ) );

		// Admin-only methods
		if ( is_admin() ) {
			
			// Admin Actions
			add_action( 'do_meta_boxes'				, array( $this , 'slide_image_box' 			) );	
			add_action( 'admin_menu'				, array( $this , 'change_slide_link' 		) );
			add_action( 'save_post'					, array( $this , 'save_meta' 				) );
			add_action( 'add_attachment'			, array( $this , 'save_meta' 				) );
			add_action( 'edit_attachment'			, array( $this , 'save_meta' 				) );
			add_action( 'manage_posts_custom_column', array( $this , 'slides_custom_columns'	) );
			
			// Admin Filters
			add_filter( 'post_updated_messages'		, array( $this , 'slide_updated_messages' 	) );
			add_filter( 'manage_edit-slide_columns'	, array( $this , 'slides_edit_columns'		) );
		}
	}
	
	/**
	 * Add a featured image size for the slides
	 * @since 0.1
	 */
	function register_slide_size() {
		
		// Register the image size
		add_image_size( 'featured-slide' , $this->width , $this->height , true );
	}

	/**
	 * Register a custom post type for Slides
	 * @version 1.0.0
	 */
	function register_slides() {

		// Labels for the backend slide publisher 
		$slide_labels = array(
			'name'					=> __('Slides', 'furiagamingcommunity_slideshow'),
			'singular_name'			=> __('Slide', 'furiagamingcommunity_slideshow'),
			'add_new'				=> __('New Slide', 'furiagamingcommunity_slideshow'),
			'add_new_item'			=> __('Add New Slide', 'furiagamingcommunity_slideshow'),
			'edit_item'				=> __('Edit Slide', 'furiagamingcommunity_slideshow'),
			'new_item'				=> __('New Slide', 'furiagamingcommunity_slideshow'),
			'view_item'				=> __('View Slide', 'furiagamingcommunity_slideshow'),
			'search_items'			=> __('Search Slides', 'furiagamingcommunity_slideshow'),
			'not_found'				=> __('No slides found', 'furiagamingcommunity_slideshow'),
			'not_found_in_trash'	=> __('No slides found in Trash', 'furiagamingcommunity_slideshow'),
			'parent_item_colon'		=> '',
			'menu_name'				=> __('Slides', 'furiagamingcommunity_slideshow'),
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
			'description'			=> __('Slides are a custom post type used for generating slideshows.', 'furiagamingcommunity_slideshow'),
			'public'				=> true,
			'publicly_queryable'	=> false,
			'exclude_from_search'	=> true,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> false,
			'menu_icon'				=> 'dashicons-images-alt',
			'menu_position'			=> 27,
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
	 * @since 0.1
	 */
	function register_slideshows() {
		
		$slideshow_tax_labels = array(			
			'name'							=> __('Slideshows', 'furiagamingcommunity_slideshow'),
			'singular_name'					=> __('Slideshow', 'furiagamingcommunity_slideshow'),
			'search_items'					=> __('Search Slideshows', 'furiagamingcommunity_slideshow'),
			'popular_items'					=> __('Popular Slideshows', 'furiagamingcommunity_slideshow'),
			'all_items'						=> __('All Slideshows', 'furiagamingcommunity_slideshow'),
			'edit_item'						=> __('Edit Slideshow', 'furiagamingcommunity_slideshow'),
			'update_item'					=> __('Update Slideshow', 'furiagamingcommunity_slideshow'),
			'add_new_item'					=> __('Add New Slideshow', 'furiagamingcommunity_slideshow'),
			'new_item_name'					=> __('New Slideshow Name', 'furiagamingcommunity_slideshow'),
			'menu_name'						=> __('Slideshows', 'furiagamingcommunity_slideshow'),
			'separate_items_with_commas'	=> __('Separate slideshows with commas', 'furiagamingcommunity_slideshow'),
			'choose_from_most_used'			=> __('Choose from the most used slideshows', 'furiagamingcommunity_slideshow'),
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

		// Register the Slideshow post taxonomy! 
		register_taxonomy( 'slideshow', 'slide', $slideshow_tax_args );	
	}
	
	/**
	 * Place the "featured image" box in the main listing, since it's the key element here.
	 * @since 0.1
	 */
	function slide_image_box() {	
		$slide_image_title = __('Set featured slide image', 'furiagamingcommunity_slideshow') . '(' . $this->width . 'x' . $this->height . ')';
		remove_meta_box( 'postimagediv', 'slide', 'side' );
		add_meta_box( 'postimagediv', $slide_image_title, 'post_thumbnail_meta_box' , 'slide', 'normal', 'high' );
	}
	
	/**
	 * Get rid of the "slug" box, show our permalink box instead.
	 * @since 0.1
	 */
	function change_slide_link() {
		remove_meta_box( 'slugdiv', 'slide', 'core' );
		add_meta_box( 'slide-settings', __('Slide Settings', 'furiagamingcommunity_slideshow'), array( $this , 'settings_box' ) , 'slide', 'normal', 'high' );
	}
	
	/**
	 * Display inputs for our custom slide meta fields
	 * @since 0.1
	 */
	function settings_box( $object , $box ) {
		wp_nonce_field( basename( __FILE__ ), 'slideshow-settings-box' );
		?>

		<p>
			<label for="slide-tabtitle"><?php _e('Slide Tab Text', 'furiagamingcommunity_slideshow'); ?></label><br />
			<input type="text" name="slide-tabtitle" id="slide-tabtitle" value="<?php echo esc_attr( get_post_meta( $object->ID, 'TabTitle', true ) ); ?>" size="55" tabindex="10" style="width: 99%;" />
		</p>
		
		<p>
			<label for="slide-permalink"><?php _e('Set Slide Permalink', 'furiagamingcommunity_slideshow'); ?></label><br />
			<input type="text" name="slide-permalink" id="slide-permalink" value="<?php echo esc_attr( get_post_meta( $object->ID, 'Permalink', true ) ); ?>" size="55" tabindex="10" style="width: 99%;" />
		</p>

		<?php 
	}

	/**
	 * Save the slide meta fields
	 * @version 1.0.0
	 */
	function save_meta( $post_id ) {
		
		// Verify the nonce before proceeding. 
		if ( !isset( $_POST['slideshow-settings-box'] ) || !wp_verify_nonce( $_POST['slideshow-settings-box'], basename( __FILE__ ) ) )
			return $post_id;
		
		// Assign names for the slide metadata 
		$meta = array(
			'TabTitle' => 	$_POST['slide-tabtitle'],
			'Permalink' => 	$_POST['slide-permalink'],
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
	 * @since 0.1
	 */
	function slide_updated_messages( $slide_messages ) {
		global $post, $post_ID;
		
		// Set some simple messages for editing slides, no post previews needed. 
		$slide_messages['slide'] = array( 
			0	=> '',
			1	=> __('Slide updated.', 'furiagamingcommunity_slideshow'),
			2	=> __('Custom field updated.', 'furiagamingcommunity_slideshow'),
			2	=> __('Custom field deleted.', 'furiagamingcommunity_slideshow'),
			4	=> __('Slide updated.', 'furiagamingcommunity_slideshow'),
			5	=> isset($_GET['revision']) ? sprintf( __('Slide restored to revision from %s', 'furiagamingcommunity_slideshow'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6	=> __('Slide published.', 'furiagamingcommunity_slideshow'),
			7	=> __('Slide saved.', 'furiagamingcommunity_slideshow'),
			8	=> __('Slide submitted.', 'furiagamingcommunity_slideshow'),
			9	=> __('Slide scheduled for', 'furiagamingcommunity_slideshow') . sprintf( ': <strong>%1$s</strong>.' , strtotime( $post->post_date ) ),
			10	=> __('Slide draft updated.', 'furiagamingcommunity_slideshow'),
			);
		return $slide_messages;
	}
	
	/**
	 * Adds the slide featured image and link to the slides page
	 * @since 0.1
	 */
	function slides_edit_columns( $columns ) {
		$columns = array(		
			'cb'			=> '<input type="checkbox" />',
			'slide'			=> __('Slide Image', 'furiagamingcommunity_slideshow'),
			'title'			=> __('Slide Title', 'furiagamingcommunity_slideshow'),
			'show'			=> __('Slideshow', 'furiagamingcommunity_slideshow'),
			'slide-link'	=> __('Slide Link', 'furiagamingcommunity_slideshow'),
			'date'			=> __('Date', 'furiagamingcommunity_slideshow'),
			);
		return $columns; 
	}
	
	/**
	 * Adds content to the custom column format
	 * @since 0.1
	 */
	function slides_custom_columns( $columns ) {
		global $post;
		switch ( $columns ) {
			case 'slide' :
				echo get_the_post_thumbnail( $post->ID , 'medium');
			break;
			
			case 'show' :
				echo get_the_term_list( $post->ID , 'slideshow' );
			break;
			
			case 'slide-link' :	
				if ( get_post_meta($post->ID, "Permalink", $single = true) != "" ) {
					echo "<a href='" . get_post_meta($post->ID, "Permalink", $single = true) . "'>" . get_post_meta($post->ID, "Permalink", $single = true) . "</a>";
				} else {
					_e('No Link', 'furiagamingcommunity_slideshow');
				}	
			break;
		}
	}
} // class FuriaGamingCommunity_Slides
register_activation_hook( basename( __FILE__ ), new FuriaGamingCommunity_Slides );


/**
 * Registers the "Slideshow" widget.
 * Uses the "Slideshow" taxonomy to query for a specified number of slides.
 *
 * @author Xavier Giménez Segovia
 * @version 1.0.0
 */
class FuriaGamingCommunity_Slideshow extends WP_Widget {

	public $error;

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'FuriaGamingCommunity_Slideshow', // Base ID
			__( 'Furia Gaming Community - Slideshow', 'furiagamingcommunity_slideshow' ), // Name
			array( 'description' => __( 'Creates a slideshow widget that gets its slides from a previous set special category.', 'furiagamingcommunity_slideshow' ),	) // Args
			);

		add_action( 'init', array( &$this, 'init' ) );
	}

	public function init(){
		
		// Add notices.
    	add_action( 'admin_notices', array( &$this, 'notices' ) );

    	if ( is_wp_error( $this->error ) )
    		$this->notices( $this->error->get_error_message(), 'warning is-dismissible' );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		
		// Defaults and arguments.
		$defaults = array (
			'number'		=> get_option('posts_per_page'),
			'slideshow'		=> ''
			);			
		$args = wp_parse_args( $args , $defaults );
		extract( $args, EXTR_SKIP );

		if ( !empty( $instance['number'] ) ) $number = $instance['number'];
		if ( !empty( $instance['slideshow'] ) ) $slideshow = $instance['slideshow'];

		// Add custom widget class.
		if( strpos($before_widget, 'class') === false )
			$before_widget = str_replace('>', 'class="slideshow">', $before_widget);
		else
			$before_widget = str_replace('class="', 'class="slideshow ', $before_widget);
		echo $before_widget;

		// In case we are already inside a post, save the $post global so it doesn't get overwritten.
		global $post;
		$temporary_post = $post;
		$slide_count = 1;

		if ( !empty($slideshow) ) :

			// Use the specified $slideshow and $number to query slides.
			$slide_loop = new WP_Query( array(
				'post_type'			=> 'slide',
				'slideshow'			=> $slideshow,
				'posts_per_page'	=> $number,
				) );
		
			// Check for slides.
			if ( $slide_loop->have_posts() ) : 

				$slides = array();
				$total_slides = $slide_loop->found_posts;

				// The Loop.
				while ( $slide_loop->have_posts() ) : $slide_loop->the_post();

					$slides[] = array(
						'number'		=> $slide_count,
						'title' 		=> $post->post_title,
						'tab'			=> get_post_meta( $post->ID , 'TabTitle' , $single = true ),
						'link'			=> get_post_meta( $post->ID , 'Permalink' , $single = true ),
						'content'		=> $post->post_content,
						'image'			=> get_the_post_thumbnail( $post->ID, 'featured-slide' )
					);

					$slide_count++;

				// End the loop.
				endwhile; 

				?>
				<div id="<?php echo $slideshow . '-slider'; ?>" class="flexslider">

					<ul class="slides">

						<?php for($i = 0; $i < $total_slides; $i++) : ?>

						<li class="slideshow-slide">
							<?php echo $slides[$i]['image']; ?>
						</li>

						<?php endfor; ?>

					</ul>		
				</div><!-- .flexslider -->
				<?php

			else: 
				// No posts.
			endif;

			// Reset post data.
			wp_reset_postdata();

		else:
			// No slideshow.
		endif;

		echo $after_widget;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		$number = !empty( $instance['number'] ) ? $instance['number'] : ''; ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of slides to display:', 'furiagamingcommunity_slideshow' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" min="1" max="9" value="<?php echo esc_attr( $number ); ?>">
			<p class="description"><?php _e( 'Set it to <strong>-1</strong> to display all slides for the selected <em>slideshow</em> in the following field.', 'furiagamingcommunity_slideshow' ); ?></p>
		</p>
		<?php $slideshows = $this->get_slideshows(); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'slideshow' ); ?>"><?php _e( 'Select a slideshow:', 'furiagamingcommunity_slideshow' ); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id( 'slideshow' ); ?>" name="<?php echo $this->get_field_name( 'slideshow' ); ?>" <?php disabled( empty( $slideshows ), true ); ?>>
				<?php if ( empty( $slideshows ) ): ?>
				<option default><?php _e( 'No slideshows set!', 'furiagamingcommunity_slideshow' ); ?></option>
				<?php else: ?>
					<?php foreach( $slideshows as $slideshow ) : ?>
					<option value="<?php echo $slideshow->term_id; ?>" <?php selected( $slideshow->term_id, $instance['slideshow'] ); ?>><?php echo $slideshow->name; ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
			<p class="description"><?php printf( __('Each <em>slideshow</em> is a custom hierarchical tag for <strong>slides</strong> post type. Follow this <a href="%s">link</a> to add or administrate <em>slideshows</em>.', 'furiagamingcommunity_slideshow' ), admin_url('edit-tags.php?taxonomy=slideshow&post_type=slide') ); ?></p>
		</p>
		<?php 
	}

	/**
	 * Get set slideshows.
	 *
	 * @param string $message Message to be printed as a notice.
	 * @param string $type Type of message to be set.
	 *
	 * @return bool|array $slideshows An array filled with all the slideshows, terms from the slideshow taxonomy or false if there are no terms.
	 */
	private function get_slideshows() {
		
		// Get all set slideshows.
		$slideshows = get_terms( 'slideshow', array( 'hide_empty' => 0 ) );

		if ( empty( $slideshows ) ) {
			
			$this->error = new WP_Error( 'no_slideshows', __( 'You need to set up some <strong>slideshows</strong> before using the widget.', 'furiagamingcommunity_slideshow' ) );
			
			return false;

		} else {
			return $slideshows;
		}
	}

	/**
	 * Print widget notices.
	 *
	 * @param string $message Message to be printed as a notice.
	 * @param string $type Type of message to be set.
	 */
	private function notices( $message, $type ) {
		if ( !empty( $message ) ) {
			$class = 'notice notice-' . $type;

			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
		}
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['number'] = ( ! empty( $new_instance['number'] ) ) ? esc_attr( $new_instance['number'] ) : $old_instance['number'];
		$instance['slideshow'] = ( ! empty( $new_instance['slideshow'] ) ) ? esc_attr( $new_instance['slideshow'] ) : $old_instance['slideshow'];

		return $instance;
	}

} // class FuriaGamingCommunity_Slideshow

// Register the widget.
add_action( 'widgets_init', function(){
	register_widget( 'FuriaGamingCommunity_Slideshow' );
});

// Register the text domain.
function FuriaGamingCommunity_Slideshow_load_textdomain() {
	load_plugin_textdomain( 'furiagamingcommunity_slideshow', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action('plugins_loaded', 'FuriaGamingCommunity_Slideshow_load_textdomain');

// Register and enqueue the included script for flexslider.
wp_register_script('furiagamingcommunity_slideshow_flexslider', plugin_dir_url(__FILE__) . 'js/jquery.flexslider.js', array('jquery'), '4.4.2', false);
wp_enqueue_script('furiagamingcommunity_slideshow_flexslider');

?>
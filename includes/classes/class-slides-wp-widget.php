<?php
/**
 * Plugin Slideshow Widget.
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Registers the "Slideshow" widget.
 * Uses the "Slideshow" taxonomy to query for a specified number of slides.
 *
 * @author Xavier Giménez Segovia
 * @version 1.0.0
 */
class Slides_WP_Widget extends WP_Widget {

	public $error;

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Slides_WP_Widget', // Base ID
			__( 'Furia Gaming Community - Slideshow', 'furiagamingcommunity_slideshows' ), // Name
			array( 'description' => __( 'Creates a slideshow widget that gets its slides from a previous set special category.', 'furiagamingcommunity_slideshows' ),    ) // Args
			);

		if ( is_admin() )
			add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * Check for errors.
	 */
	public function init(){

		// Add notices.
		if ( is_admin() )
			add_action( 'admin_notices', array( &$this, 'notices' ),10 ,2 );

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
			'number'        => get_option('posts_per_page'),
			'position'      => 'center',
			'slideshow'     => ''
			);
		$args = wp_parse_args( $args , $defaults );
		extract( $args, EXTR_SKIP );

		if ( !empty( $instance['number'] ) )    $number    = $instance['number'];
		if ( !empty( $instance['position'] ) )  $slideshow = $instance['position'];
		if ( !empty( $instance['slideshow'] ) ) $slideshow = $instance['slideshow'];

		// Add custom widget class.
		if( strpos($before_widget, 'class') === false )
			$before_widget = str_replace('>', 'class="slideshow">', $before_widget);
		else
			$before_widget = str_replace('class="', 'class="slideshow ', $before_widget);
		echo $before_widget;

		$slide_count = 1;

		if ( !empty($slideshow) ) :

			// Use the specified $slideshow and $number to query slides.
			$slide_query = new WP_Query( array(
				'post_type'      => 'slide',
				'posts_per_page' => $number,
				'tax_query'      => array(
					'taxonomy'   => 'slideshow',
					'field'      => 'term_id',
					'terms'      => $slideshow
				)
			));

			// Check for slides.
			if ( $slide_query->have_posts() ) :

				$slides = array();
				$total_slides = $slide_query->found_posts;

				// The Loop.
				while ( $slide_query->have_posts() ) : $slide_query->the_post();
					$slides[] = array(
						'number'         => $slide_count,
						'title'          => $slide_query->post->post_title,
						'tab'            => get_post_meta( $slide_query->post->ID , 'TabTitle' , $single = true ),
						'link'           => get_post_meta( $slide_query->post->ID , 'Permalink' , $single = true ),
						'content'        => $slide_query->post->post_content,
						'position'       => get_post_meta( $slide_query->post->ID , 'TextPosition' , $single = true ),
						'image'          => get_post_first_image(),
						'featured_image' => get_the_post_thumbnail_url( $slide_query->post->ID, 'featured-slide' ),
						'width'          => get_post_meta( $slide_query->post->ID , 'Width' , $single = true )
						);

					$slide_count++;
				// End the loop.
				endwhile;

				?>
				<section class="cd-hero">
					<ul class="cd-hero-slider autoplay">
						<?php for($i = 0; $i < $total_slides; $i++) : ?>

							<?php $slides[$i]['content'] = apply_filters('the_content', $slides[$i]['content']); ?>

							<li<?php if ( $i == 0 ) : ?> class="selected"<? endif; ?> style="background-image: url('<?php echo $slides[$i]['featured_image']; ?>');">
								<?php if ( $slides[$i]['width'] === 'full-width'): ?>
									<div class="cd-full-width">
										<h2 class="cd-title"><a href="<?php echo $slides[$i]['link']; ?>" target="_blank" ><?php echo $slides[$i]['title']; ?></a></h2>
										<?php $slides[$i]['content'] = apply_filters('the_content', $slides[$i]['content']); ?>
										<?php echo $slides[$i]['content']; ?>
										<a href="<?php echo $slides[$i]['link']; ?>" class="cd-btn"><?php _e('Read More', 'furiagamingcommunity_slideshows'); ?></a>
									</div><!-- .cd-cd-full-width -->
								<?php else: ?>
									<div class="cd-cd-half-width<?php if ( !empty( $image ) ) echo ' cd-img-container'; ?>">
										<?php if ( $slides[$i]['position'] === 'left' ) : ?>
											<h2 class="cd-title"><a href="<?php echo $slides[$i]['link']; ?>" target="_blank" ><?php echo $slides[$i]['title']; ?></a></h2>
											<?php echo $slides[$i]['content']; ?>
											<a href="<?php echo $slides[$i]['link']; ?>" class="cd-btn"><?php _e('Read More', 'furiagamingcommunity_slideshows'); ?></a>
										<?php elseif ( !empty( $image ) ): ?>
											<img src="<?php echo $image; ?>" alt="<?php echo $slides[$i]['title']; ?>"/>
										<?php endif; ?>
									</div><!-- .cd-half-width -->
									<div class="cd-cd-half-width<?php if ( !empty( $image ) ) echo ' cd-img-container'; ?>">
										<?php if ( $slides[$i]['position'] === 'right' ) : ?>
											<h2 class="cd-title"><a href="<?php echo $slides[$i]['link']; ?>" target="_blank" ><?php echo $slides[$i]['title']; ?></a></h2>
											<?php echo $slides[$i]['content']; ?>
											<a href="<?php echo $slides[$i]['link']; ?>" class="cd-btn"><?php _e('Read More', 'furiagamingcommunity_slideshows'); ?></a>
										<?php elseif ( !empty( $image ) ): ?>
											<img src="<?php echo $image; ?>" alt="<?php echo $slides[$i]['title']; ?>"/>
										<?php endif; ?>
									</div><!-- .cd-half-width -->
								<?php endif; ?>
							</li><!-- .cd-slide-<?php echo $i+1; ?> -->

						<?php endfor; ?>
					</ul> <!-- .cd-hero-slider -->

					<div class="cd-slider-nav">
						<nav>
							<ul>
								<?php for ( $i = 0; $i < $total_slides; $i++ ) : ?>
									<li<?php if ( $i == 0 ) : ?> class="selected"<? endif; ?>><a href="#<?php echo $i; ?>"><?php echo $slides[$i]['tab']; ?></a></li>
								<?php endfor; ?>
							</ul>
						</nav>
					</div> <!-- .cd-slider-nav -->
				</section> <!-- .cd-hero -->
				<?php

			else:
				// No posts.
				$this->notices( __('There aren\'t any set posts for the selected slideshow.','furiagamingcommunity_slideshows'), 'info' );
			endif;

			// Reset post data.
			wp_reset_postdata();

		else:
			// No slideshow.
			$this->notices( __('No slideshow set!','furiagamingcommunity_slideshows'), 'warning' );
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

		$number = ! empty( $instance['number'] ) ? $instance['number'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of slides to display:', 'furiagamingcommunity_slideshows' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" min="-1" max="9" value="<?php echo esc_attr( $number ); ?>">
			<span class="description"><?php _e( 'Set to <strong>-1</strong> to display all slides.', 'furiagamingcommunity_slideshows' ); ?></span>
		</p>
		<?php
		$slideshows = $this->get_slideshows();
		?>
		<p><?php printf( __('Each <em>slideshow</em> is a custom hierarchical tag for <strong>slides</strong> post type. Follow this <a href="%s">link</a> to add or administrate <em>slideshows</em>.', 'furiagamingcommunity_slideshows' ), admin_url('edit-tags.php?taxonomy=slideshow&post_type=slide') ); ?></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'slideshow' ); ?>"><?php _e( 'Select a slideshow:', 'furiagamingcommunity_slideshows' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'slideshow' ); ?>" name="<?php echo $this->get_field_name( 'slideshow' ); ?>" <?php disabled( empty( $slideshows ), true ); ?>>
				<?php if ( empty( $slideshows ) ): ?>
					<option default><?php _e( 'No slideshows set!', 'furiagamingcommunity_slideshows' ); ?></option>
				<?php else: foreach( $slideshows as $slideshow ) : ?>
					<option value="<?php echo $slideshow->term_id; ?>" <?php selected( $slideshow->term_id, ! empty($instance['slideshow']) ? $instance['slideshow'] : '' ); ?>><?php echo $slideshow->name; ?></option>
				<?php endforeach; endif; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * Get set slideshows.
	 *
	 * @return bool|array $slideshows An array filled with all the slideshows, terms from the slideshow taxonomy or false if there are no terms.
	 */
	private function get_slideshows() {

		// Get all set slideshows.
		$slideshows = get_terms( 'slideshow', array( 'hide_empty' => 0 ) );

		if ( empty( $slideshows ) ) {

			$this->error = new WP_Error( 'no_slideshows', __( 'You need to set up some <strong>slideshows</strong> before using the widget.', 'furiagamingcommunity_slideshows' ) );

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
	public function notices( $message, $type = 'info' ) {
		if ( !empty( $message ) && !empty( $message ) ) {
			$class = 'notice notice-' . $type;

			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
		} else {
			// No notice
			return false;
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
		$instance              = array();
		$instance['number']    = ( ! empty( $new_instance['number'] ) ) ? $new_instance['number'] : $old_instance['number'];
		$instance['slideshow'] = ( ! empty( $new_instance['slideshow'] ) ) ? esc_attr( $new_instance['slideshow'] ) : $old_instance['slideshow'];

		return $instance;
	}

} // class Slides_WP_Widget

?>

<?php

function xamin_contact_info() {
    register_widget( 'iq_contact' );
}
add_action( 'widgets_init', 'xamin_contact_info' );

/*-------------------------------------------
		iqonic Contact Information widget
--------------------------------------------*/
class iq_contact extends WP_Widget {

	function __construct() {
		parent::__construct(

			// Base ID of your widget
			'iq_contact',

			// Widget name will appear in UI
			esc_html('Xamin Contact', 'xamin'),

			// Widget description
			array( 'description' => esc_html( 'Xamin Contact. ', 'xamin' ), )
		);
	}

	// Creating widget front-end

	public function widget( $args, $instance ) {

		global $wp_registered_sidebars;
		$xamin_option = get_option('xamin_options');

        if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : esc_html( 'Contact Info', 'xamin' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$phone = isset( $instance['phone'] ) ? $instance['phone'] : false;
		$email = isset( $instance['email'] ) ? $instance['email'] : false;
		$address = isset( $instance['address'] ) ? $instance['address'] : false;

		//$args['after_widget'];

		/* here add extra display item  */
		?>
		<div class="widget">
		<?php

		if ( $title ) {
			echo ($args['before_title'] . $title . $args['after_title']);
		}
		?>
			<div class="row">
				<div class="col-sm-12">
					<ul class="iq-contact">
						<?php
						if ( $phone ) :
						?>
						<li>

						<a href="tel:<?php echo str_replace(str_split('(),-" '), '',$xamin_option['phone']); ?>"><i class="fa fa-phone"></i><span><?php echo esc_attr($xamin_option['phone']); ?></span></a>
						</li>
						<?php endif; ?>
						<?php
						if ( $email ) :
						?>
						<li>

						<a href="mailto:<?php echo esc_html($xamin_option['email']); ?>"><i class="fa fa-envelope"></i><span><?php echo esc_attr($xamin_option['email']); ?></span></a>
						</li>
						<?php endif; ?>
						<?php
						if ( $address ) :
						?>
						<li>

						<li>
							<a><i class="fa fa-home"></i><span><?php $address = $xamin_option['address']; echo esc_attr($address); ?></span></a>
						</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
			</div>
	<?php
	}

	// Widget Backend
	public function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$phone    = isset( $instance['phone'] ) ? (bool) $instance['phone'] : false;
		$email = isset( $instance['email'] ) ? (bool) $instance['email'] : false;
		$address = isset( $instance['address'] ) ? (bool) $instance['address'] : false;
		?>

		<p><label for="<?php echo esc_html($this->get_field_id( 'title','xamin' )); ?>"><?php esc_html_e( 'Title:','xamin' ); ?></label>
		<input class="widefat" id="<?php echo esc_html($this->get_field_id( 'title','xamin' )); ?>" name="<?php echo esc_html($this->get_field_name( 'title','xamin')); ?>" type="text" value="<?php echo esc_html($title,'xamin'); ?>" /></p>

		<p><input class="checkbox" type="checkbox"<?php checked( $phone ); ?> id="<?php echo esc_html($this->get_field_id( 'phone','xamin' )); ?>" name="<?php echo esc_html($this->get_field_name( 'phone','xamin' )); ?>" />
		<label for="<?php echo esc_html($this->get_field_id( 'phone','xamin' )); ?>"><?php esc_html_e( 'Display Phone Number?','xamin' ); ?></label></p>

		<p><input class="checkbox" type="checkbox"<?php checked( $email ); ?> id="<?php echo esc_html($this->get_field_id( 'email','xamin' )); ?>" name="<?php echo esc_html($this->get_field_name( 'email','xamin' )); ?>" />
		<label for="<?php echo esc_html($this->get_field_id( 'email','xamin' )); ?>"><?php esc_html_e( 'Display Email?','xamin' ); ?></label></p>

		<p><input class="checkbox" type="checkbox"<?php checked( $address ); ?> id="<?php echo esc_html($this->get_field_id( 'address','xamin' )); ?>" name="<?php echo esc_html($this->get_field_name( 'address','xamin' )); ?>" />
		<label for="<?php echo esc_html($this->get_field_id( 'address','xamin' )); ?>"><?php esc_html_e( 'Display Address?','xamin' ); ?></label></p>

		<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['phone'] = isset( $new_instance['phone'] ) ? (bool) $new_instance['phone'] : false;
		$instance['email'] = isset( $new_instance['email'] ) ? (bool) $new_instance['email'] : false;
		$instance['address'] = isset( $new_instance['address'] ) ? (bool) $new_instance['address'] : false;
		$instance['iq-contact'] = $new_instance['iq-contact'];
        return $instance;
	}
}
/*---------------------------------------
		Class wpb_widget ends here
----------------------------------------*/
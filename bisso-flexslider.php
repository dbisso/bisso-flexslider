<?php
/*
Plugin Name: Bisso FlexSlider
Plugin URI: http://danisadesigner.com/wordpress/flexslider
Description: Description
Version: 0.1
Author: Dan Bissonnet
Author URI: http://danisadesigner.com/
*/

/**
 * Copyright (c) 2012 Your Name. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

class BissoFlexSlider {
	static $_hooker;
	static $settings = array();
	static $animation_presets = array();

	function bootstrap( $hooker = null ) {
		try {
		 	if ( $hooker ) {
		 		self::$_hooker = $hooker;
		 		self::$_hooker->hook( __CLASS__, 'bisso_flexslider' );
		 	} else {
		 		throw new Exception( 'Hooking class for plugin not specified.' , 1);
		 	}
	 	} catch ( Exception $e ) {
	 		wp_die( plugin_basename( __FILE__ ) . ' plugin bootsrap error: ' . $e->getMessage(), plugin_basename( __FILE__ ) . ' plugin bootsrap error: ' );
	 	}

		self::$settings = self::wp_parse_args_recursive( get_option( 'bisso_flexslider_options', array() ),
			array(
				'enable' => false,
				'flexslider_settings' => array(
					'animation' => 'fade',
					'slideshow_speed' => 7000,
					'animation_speed' => 600,
					'slideshow' => true
				)
			) );
		self::$animation_presets = array(
			'slide' => __( 'Slide', 'bisso-flexslider' ),
			'fade'  => __( 'Fade', 'bisso-flexslider' )
		);
	}

	function action_wp_enqueue_scripts() {
		wp_enqueue_script( 'jquery-flexslider', plugins_url( 'lib/flexslider/jquery.flexslider-min.js', __FILE__ ), array( 'jquery' ), 2.1, true );
		wp_enqueue_style( 'jquery-flexslider-style', plugins_url( 'lib/flexslider/flexslider.css', __FILE__ ), null, 2.1 );

		wp_localize_script( 'jquery-flexslider', 'bissoFlexsliderSettings', self::camelize_array( self::get_post_settings() ) );
	}

	function action_add_meta_boxes () {
		$post_types = get_post_types();

		foreach ($post_types as $name => $post_type) {
			if ( in_array( $name, apply_filters( 'bisso_flexslider_post_types', array( 'post', 'page' ) ) ) ) add_meta_box( 'bisso-flexslider-options', __( 'Slideshow Options', 'bisso-flexslider' ), array( __CLASS__, 'meta_box_render' ), null, $context = 'advanced', $priority = 'default', null );
		}
	}

	function meta_box_render() {
		$post_settings = self::get_post_settings();

?><p class='meta-options'>
		<label for="bisso_flexslider_enable" class="selectit"><input name="bisso_flexslider[enable]" <?php checked( $post_settings['enable']) ?> type="checkbox" id="bisso_flexslider_enable" value="true"> Show slideshow of gallery images.</label><br />

		<label for="bisso_flexslider_settings_slideshow" class="selectit"><input name="bisso_flexslider[flexslider_settings][slideshow]" <?php checked( $post_settings['flexslider_settings']['slideshow'] ) ?> type="checkbox" id="bisso_flexslider_settings_slideshow" value="true"> <?php _e( 'Automatic slideshow', 'bisso-flexslider') ?></label><br />

		<label for="bisso_flexslider_settings_animation" class="selectit"><?php _e( 'Animation', 'bisso-flexslider' ) ?>
			<select name="bisso_flexslider[flexslider_settings][animation]">
			<?php foreach ( self::$animation_presets as $animation_option => $label ): ?>
				<option  <?php selected( $post_settings['flexslider_settings']['animation'], $animation_option ) ?> type="checkbox" id="bisso_flexslider_settings_animation" value="<?php echo $animation_option ?>"><?php echo $label ?></option>
			<?php endforeach; ?>
			</select>
		</label><br />

		<label for="bisso_flexslider_settings_slideshow_speed">
			<?php _e( 'Slideshow Speed', 'bisso-flexslider' ) ?>
			<input name="bisso_flexslider[flexslider_settings][slideshow_speed]" value="<?php echo $post_settings['flexslider_settings']['slideshow_speed'] ?>" type="text" id="bisso_flexslider_settings_slideshow_speed" />
			<span class='description'><?php _e( 'Time in milliseconds to display each image', 'bisso-flexslider' ) ?></span>
		</label><br />

		<label for="bisso_flexslider_settings_animation_speed">
			<?php _e( 'Animation Speed', 'bisso-flexslider' ) ?>
			<input name="bisso_flexslider[flexslider_settings][animation_speed]" value="<?php echo $post_settings['flexslider_settings']['animation_speed'] ?>" type="text" id="bisso_flexslider_settings_animation_speed" />
			<span class='description'><?php _e( 'Time in milliseconds it takes to change between images', 'bisso-flexslider' ) ?></span>
		</label><br />

	</p>
<?php
	}

	function action_save_post ( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		// if ( !wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename( __FILE__ ) ) )
			// return;

		// Check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ) )
				return;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) )
				return;
		}

		$data['enable'] = self::boolify( $_POST['bisso_flexslider']['enable'] ) ;
		if ( !empty( $_POST['bisso_flexslider']['flexslider_settings']['slideshow_speed'] ) ) $data['flexslider_settings']['slideshow_speed'] = intval($_POST['bisso_flexslider']['flexslider_settings']['slideshow_speed']);
		if ( !empty( $_POST['bisso_flexslider']['flexslider_settings']['animation_speed'] ) ) $data['flexslider_settings']['animation_speed'] = intval($_POST['bisso_flexslider']['flexslider_settings']['animation_speed']);
		$data['flexslider_settings']['slideshow'] = self::boolify($_POST['bisso_flexslider']['flexslider_settings']['slideshow']);
		if ( in_array( $_POST['bisso_flexslider']['flexslider_settings']['animation'], array_keys( self::$animation_presets ) ) ) $data['flexslider_settings']['animation'] = $_POST['bisso_flexslider']['flexslider_settings']['animation'];

		// TODO: Validate and sanitize
		update_post_meta( $post_id, 'bisso_flexslider_options', $data );
	}

	/**
	 * Gets post level plugin settings, falling back to sitewide defaults
	 * @return array Post level settings
	 */
	function get_post_settings() {
		global $post;

		$post_settings  = get_post_meta($post->ID, 'bisso_flexslider_options', true);
		return self::wp_parse_args_recursive( $post_settings, self::$settings );
	}


	function shortcode_bisso_flexslider( $atts, $content = '', $tag ) {
		global $post;

		$defaults = array(
			'id' => $post->ID,
		);

		extract( shortcode_atts( $defaults, $atts ) );
		$attachments = get_posts( array(
			'numberposts'		=>	-1,
			'offset'			=>	0,
			// 'category'			=>	,
			'orderby'			=>	'post_order',
			// 'order'				=>	'ASC',
			// 'include'			=>	,
			// 'exclude'			=>	,
			// 'meta_key'			=>	,
			// 'meta_value'		=>	,
			'post_type'			=>	'attachment',
			// 'post_mime_type'	=>	'image',
			'post_parent'		=>	$id,
			// 'post_status'		=>	'publish'
		) );

		$content  = '<div class="' . implode( ' ', apply_filters( 'bisso_flexslider_class', array( 'flexslider' ))) . '"><ul class="slides">';

		$caption_class  = ($classes = implode( ' ', apply_filters( 'bisso_flexslider_caption_class', array( 'flex-caption' ) ) ) ) ? "class='$classes'" : '';

		$slide_class  = ($classes = implode( ' ', apply_filters( 'bisso_flexslider_slide_class', array() ) ) ) ? "class='$classes'" : '';

		foreach ($attachments as $key => $attachment) {

			$caption = !empty( $attachment->post_excerpt ) ? "<p $caption_class>{$attachment->post_excerpt}</p>" : '';
			$content .= "<li $slide_class>" . wp_get_attachment_image( $attachment->ID,  'large', false) . $caption . '</li>';
		}

		$content .= '</ul></div>';

		return do_shortcode( $content );
	}

	function action_wp_footer ( ) {
		echo "<script type='text/javascript'>
jQuery('document').ready( function($){
	$('.flexslider').flexslider(bissoFlexsliderSettings.flexsliderSettings);
})
		</script>";
	}

	function filter_the_content ( $content ) {
		global $post;

		$post_settings = self::get_post_settings();
		if ( $post_settings['enable'] ) return  $content . do_shortcode( '[bisso-flexslider id=' . $post->ID . ']' );

		return $content;
	}

	function camelize_array( $array ) {
		foreach ($array as $key => $value) {
			unset( $array[$key] );
			$array[self::camelize( $key )] = is_array( $value ) ? self::camelize_array( $value ) : $value;
		}
		return $array;
	}

	function camelize( $string, $pascalCase = false ) {
		$string = str_replace(array('-', '_'), ' ', $string);
		$string = ucwords($string);
		$string = str_replace(' ', '', $string);

		if (!$pascalCase) {
			return lcfirst($string);
		}

		return $string;
	}

	function wp_parse_args_recursive() {
		$arrays = func_get_args();
        $base = array_pop($arrays);

        foreach ($arrays as $array) {
            reset($base); //important
            while (list($key, $value) = @each($array)) {
                if (is_array($value) && @is_array($base[$key])) {
                    $base[$key] = self::wp_parse_args_recursive($value, $base[$key]);
                } else {
                    $base[$key] = $value;
                }
            }
        }

        return $base;
	}

	function boolify( $value ) {
		if ( empty( $value ) ) return false;
		return !is_bool( $value ) ? 'true' === $value : $value;
	}

}
try {
	if ( class_exists( 'Bisso_Hooker' ) ) {
		BissoFlexSlider::bootstrap( new Bisso_Hooker );
	} else {
		throw new Exception( "Class Bisso_Hooker not found. Check that the plugin is installed.", 1 );
	}
} catch ( Exception $e ) {
	wp_die( $e->getMessage(), $title = 'Plugin Exception' );
}
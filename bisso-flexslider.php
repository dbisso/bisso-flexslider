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
	static $wp_hook_prefix;
	static $settings = array();

	function bootstrap() {
		self::$wp_hook_prefix = 'bisso-flexslider';
		self::$_hooker = new Bisso_Hooker( __CLASS__, 'bisso-flexslider' );
		self::$settings = wp_parse_args( get_option( 'bisso_flexslider_options', array() ),
			array(
				'flexslider_settings' => array(
					'animation' => 'fade',
					'slideshow_speed' => 7000,
					'animation_speed' => 600
				)
			) );
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

		$animation_options = array(
			'slide' => __( 'Slide', 'bisso-flexslider' ),
			'fade'  => __( 'Fade', 'bisso-flexslider' )
		);

?><p class='meta-options'>
		<label for="bisso_flexslider_enable" class="selectit"><input name="bisso_flexslider[enable]" <?php checked( $post_settings['enable'], 'true' ) ?> type="checkbox" id="bisso_flexslider_enable" value="true"> Show slideshow of gallery images.</label><br />
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

		$data = $_POST['bisso_flexslider'];
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

		return wp_parse_args( $post_settings, self::$settings );
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
			// 'post_parent'		=>	$id,
			// 'post_status'		=>	'publish'
		) );

		$content  = '<div class="' . implode( ' ', apply_filters( 'bisso_flexslider_class', array( 'flexslider' ))) . '"><ul class="slides">';

		foreach ($attachments as $key => $attachment) {

			$caption = !empty( $attachment->post_excerpt ) ? "<p class='flex-caption'>{$attachment->post_excerpt}</p>" : '';
			$content .= '<li>' . wp_get_attachment_image( $attachment->ID,  'large', false ) . $caption . '</li>';
		}

		$content .= '</ul></div>';

		return do_shortcode( $content );
	}

	function action_wp_head ( ) {
		echo "<script type='text/javascript'>
jQuery('document').ready( function($){
	$('.flexslider').flexslider(bissoFlexsliderSettings.flexsliderSettings);
})
		</script>";
	}

	function filter_the_content ( $content ) {
		$post_settings = self::get_post_settings();
		if ( $post_settings['enable'] ) return  $content . do_shortcode( '[bisso-flexslider]' );

		return $content;
	}

	function camelize_array( $array ) {
		foreach ($array as $key => $value) {
			$array[self::camelize( $key )] = is_array( $value ) ? self::camelize_array( $value ) : $value;
			unset( $array[$key] );
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

}

BissoFlexSlider::bootstrap();
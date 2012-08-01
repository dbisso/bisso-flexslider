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

	function bootstrap() {
		self::$wp_hook_prefix = 'bisso-flexslider';
		self::$_hooker = new Bisso_Hooker( __CLASS__, 'bisso-flexslider' );
	}

	function action_wp_enqueue_scripts() {
		wp_enqueue_script( 'jquery-flexslider', plugins_url( 'lib/flexslider/jquery.flexslider-min.js', __FILE__ ), array( 'jquery' ), 2.1, true );
		wp_enqueue_style( 'jquery-flexslider-style', plugins_url( 'lib/flexslider/flexslider.css', __FILE__ ), null, 2.1 );
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

		// var_dump($attachments);

		$content  = '<div class="flexslider"><ul class="slides">';

		foreach ($attachments as $key => $attachment) {
			$content .= '<li>' . wp_get_attachment_image( $attachment->ID,  'large', false ) . '</li>';
		}

		$content .= '</ul></div>';

		return do_shortcode( $content );
	}

	function action_wp_head ( ) {
		echo "<script type='text/javascript'>
jQuery('document').ready( function($){
	$('.flexslider').flexslider();
})
		</script>";
	}

}

BissoFlexSlider::bootstrap();
<?php
/*
Plugin Name: Woocommerce Image Product Annotator
Description: Adds the ability to create annotated images and then edit the annotations later.
Version: 1.0
  Author: Tolotra Samuel
  Author URI: http://www.tolotranet.com
  License: GPLv3
 */

register_activation_hook( __FILE__, 'wcia_quick_view_activate' );

register_activation_hook( __FILE__, 'create_uploadr_page' );

function wcia_quick_view_activate() {

	add_option( 'wcia_view_install_date', date( 'Y-m-d G:i:s' ), '', 'yes' );

	$data = array(
		'enable_quick_view' => '1',
		'enable_mobile'     => '1',
		'button_lable'      => 'Shop the look'
	);
	add_option( 'wcia_options', $data, '', 'yes' );

	$data = array(
		'modal_bg'       => '#fff',
		'close_btn'      => '#95979c',
		'close_btn_bg'   => '#4C6298',
		'navigation_bg'  => 'rgba(255, 255, 255, 0.2)',
		'navigation_txt' => '#fff'
	);
	add_option( 'wcia_style', $data, '', 'yes' );
}


register_deactivation_hook( __FILE__, 'wcia_quick_view_deactivate' );
function wcia_quick_view_deactivate() {

	delete_option( 'wcia_style' );
	delete_option( 'wcia_options' );
	delete_option( 'wcia_view_install_date' );

}


add_action( 'plugins_loaded', 'wcia_load_class_files' );

function wcia_load_class_files() {

	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		require_once 'classes/class-wcia_front_product.php';
		require_once 'classes/class-wcia_backend_settings.php';

		load_plugin_textdomain( 'woocommerce-image-annotation', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );


		$wcia_plugin_dir_url = plugin_dir_url( __FILE__ );
		$data                = get_option( 'wcia_options' );
		$load_backend        = new wcia_backend_settings( $wcia_plugin_dir_url );
		$enable_mobile       = ( $data['enable_mobile'] === '1' ) ? true : false;


		if ( $load_backend->mobile_detect() ) {

			if ( $enable_mobile && ( $data['enable_quick_view'] == 1 ) ) {

				$load_frontend = new wcia_front_product( $wcia_plugin_dir_url );
			}

		} else {

			if ( $data['enable_quick_view'] == 1 ) {
				$load_frontend = new wcia_front_product( $wcia_plugin_dir_url );
			}

		}

	}
}


//Add settings link on plugin page
function wcia_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=woocommerce-image-annotation">Settings</a>';
	array_unshift( $links, $settings_link );

	return $links;
}


function create_uploadr_page() {

	$post_id = - 1;

	// Setup custom vars
	$author_id = 1;
	$slug      = 'wcia-gallery-annotation';
	$title     = 'Gallery Annotation';

	// Check if page exists, if not create it
	if ( null == get_page_by_title( $title ) ) {

		$uploader_page = array(
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_author'    => $author_id,
			'post_name'      => $slug,
			'post_title'     => $title,
			'post_status'    => 'publish',
			'post_type'      => 'page'
		);

		$post_id = wp_insert_post( $uploader_page );


		if ( ! $post_id ) {

			wp_die( 'Error creating template page' );

		} else {

			update_post_meta( $post_id, '_wp_page_template', 'gallery-annotation.php' );

		}
	} // end check if

}

add_action( 'page_template', 'uploadr_redirect' );

function uploadr_redirect( $template ) {
	$page_plugin_gallery = 'Gallery Annotation';
	$plugindir           = dirname( __FILE__ );
	if ( is_page_template( 'gallery-annotation.php' ) ) {

		$template = $plugindir . '/templates/gallery-annotation.php';
	} else {
		if ( ! is_admin() ) {
			require_once( ABSPATH . 'wp-admin/includes/post.php' );
		};
		if ( post_exists( $page_plugin_gallery ) ) {
			$page = get_page_by_title( $page_plugin_gallery );
			//page is registered in post db but not in post meta
			update_post_meta( $page->ID, '_wp_page_template', 'gallery-annotation.php' );
			$template = $plugindir . '/templates/gallery-annotation.php';
		}
	}

	return $template;
}


$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'wcia_settings_link' );


//Add file for plugin setup (post type and script registration)

require( 'classes/class-wcia_admin.php' );

//Adds image annotation area
require( 'classes/class-wcia_admin_metaboxes.php' );

//Adds front end functions like shortcodes and script registration
require( 'classes/class-wcia_front_image.php' );
<?php

class WCIA_Admin {

	function __construct() {
		//Registers the post type
		add_action( 'init', array( $this, 'register_post_type' ) );

		//Adds scripts to be used
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles_scripts' ) );

		//Adds current object (if one exists) to be edited to header
		add_action( 'admin_print_scripts', array( $this, 'add_current_json' ) );
		add_action( 'admin_print_scripts', array( $this, 'add_current_json_object' ) );
		add_filter( 'single_template', array( $this, 'wcia_hook_single_custom_template' ) );
		add_filter( 'archive_template', 'yourplugin_get_custom_archive_template' );

	}

	/* Filter the single_template with our custom function*/

	function yourplugin_get_custom_archive_template( $template ) {
		global $wp_query;
		if ( is_post_type_archive( 'annotation' ) ) {
			$template_url = plugins_url( 'templates/archive-annotation.php', __FILE__ );
			if ( file_exists( $template_url ) ) {
				;
			}
			{
				echo $template_url;
				return ( dirname( __FILE__ ) . '/../templates/archive-annotation.php' );
			}
		}

		return $template;
	}

	function wcia_hook_single_custom_template( $single ) {
		global $wp_query, $post;

		/* Checks for single templates by post type */
		if ( $post->post_type == "annotation" ) {
			$template_url = plugins_url( '../templates/single-annotation.php', __FILE__ );
			//echo $template_url;
			if ( $template_url )  {
				return ( dirname( __FILE__ ) . '/../templates/single-annotation.php' );
			}
		}

		return $single;
	}

	public function register_post_type() {

		$labels = array(
			'name'               => _x( 'Annotations', 'post type general name', 'wp_image_annotator' ),
			'singular_name'      => _x( 'Annotation', 'post type singular name', 'wp_image_annotator' ),
			'menu_name'          => _x( 'Image Product Annotator', 'admin menu', 'wp_image_annotator' ),
			'name_admin_bar'     => _x( 'Annotation', 'add new on admin bar', 'wp_image_annotator' ),
			'add_new'            => _x( 'Add New', 'annotation', 'wp_image_annotator' ),
			'add_new_item'       => __( 'Add New Annotation', 'wp_image_annotator' ),
			'new_item'           => __( 'New Annotation', 'wp_image_annotator' ),
			'edit_item'          => __( 'Edit Annotation', 'wp_image_annotator' ),
			'view_item'          => __( 'View Annotation', 'wp_image_annotator' ),
			'all_items'          => __( 'All Annotations', 'wp_image_annotator' ),
			'search_items'       => __( 'Search Annotations', 'wp_image_annotator' ),
			'parent_item_colon'  => __( 'Parent Annotations:', 'wp_image_annotator' ),
			'not_found'          => __( 'No annotations found.', 'wp_image_annotator' ),
			'not_found_in_trash' => __( 'No annotations found in Trash.', 'wp_image_annotator' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'wp_image_annotator' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'annotation' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-images-alt2',
			'supports'           => array( 'title', 'revisions' )
		);

		register_post_type( 'annotation', $args );
	}

	/**
	 * Adds scripts and styles.
	 *
	 * @param WP_Post $hook Current page.
	 */

	function admin_styles_scripts( $hook ) {

		if ( $hook !== 'edit.php' && $hook !== 'post.php' && $hook !== 'post-new.php' ) {
			return;
		}

		if ( get_post_type() !== 'annotation' ) {
			return;
		}
		wp_enqueue_style( 'thickbox' );
//	    wp_enqueue_style('wcia-admin-style', plugins_url('../admin/css/jquery-ui.theme.css', __FILE__));
		wp_enqueue_style( 'wcia-admin-fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css' );
		wp_enqueue_style( 'wcia-admin-style', plugins_url( '../css/admin/css/style.css', __FILE__ ) );
		wp_enqueue_style( 'wcia-admin-style', plugins_url( '../css/admin/css/global.css', __FILE__ ) );
		wp_enqueue_style( 'wcia-admin-input', plugins_url( '../css/admin/css/input.css', __FILE__ ) );

		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-draggable' );

		wp_enqueue_script( 'wia-admin-product_relationship', plugins_url( '../js/admin/js/input.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'wcia-admin-imagesloaded', plugins_url( '../lib/imagesLoaded/imagesloaded.pkgd.min.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'wcia-admin-scripts', plugins_url( '../js/admin/js/script.js', __FILE__ ), array(
			'jquery',
			'media-upload',
			'thickbox'
		) );


	}

	function add_current_json() {

		if ( ! ( get_current_screen()->base === 'post' ) && ! ( get_current_screen()->post_type === 'annotation' ) ) {
			return;
		}

		$id = get_the_ID();

		echo "<script type='text/javascript'>\n";
		echo 'var currentWIPAObject = [' . get_post_meta( $id, "wcia_annotation_data", true ) . ', ' . get_post_meta( $id, "wcia_annotation_canvas_size", true ) . '];';
		echo "\n</script>";
	}

	function add_current_json_object() {

		if ( ! ( get_current_screen()->base === 'post' ) && ! ( get_current_screen()->post_type === 'annotation' ) ) {
			return;
		}

		$id = get_the_ID();

		echo "<script type='text/javascript'>\n";
		echo 'var currentWIPAObjectData = [' . get_post_meta( $id, "wcia_annotation_data_object", true ) . ', ' . get_post_meta( $id, "wcia_annotation_canvas_size", true ) . '];';
		echo "\n</script>";
	}

}

new WCIA_Admin();
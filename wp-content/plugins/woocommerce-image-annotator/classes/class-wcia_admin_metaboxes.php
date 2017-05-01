<?php

class WCIA_Admin_Metaboxes {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'wcia_register_edit_area' ) );
		add_action( 'save_post', array( $this, 'wcia_save_meta_box' ) );
		add_action( 'admin_init', array( $this, 'add_ajax_actions' ) );
		add_action( 'input_admin_head', array( $this, 'input_admin_head' ) );


	}

	function add_ajax_actions() {
		add_action( 'wp_ajax_acf_fields_relationship_query_posts', array( $this, 'query_posts' ) );
		add_action( 'wp_ajax_nopriv_acf_fields_relationship_query_posts', array( $this, 'query_posts' ) );
	}

	function wcia_register_edit_area() {
		add_meta_box( 'wcia-meta',
			__( 'Image Annotation', 'image-annotator' ),
			array( $this, 'wcia_display_callback' ),
			'annotation'
		);

		add_meta_box( 'wcia-meta',
			__( 'Image Annotation', 'image-annotator' ),
			array( $this, 'wcia_display_callback' ),
			'annotation'
		);
		add_meta_box( 'wcia-meta-product',
			__( 'Select Product', 'image-annotator' ),
			array( $this, 'wcia_display_product_callback' ),
			'annotation'
		);
	}

	function input_admin_head() {
		// global
		global $wp_version, $post;


		// vars
		$toolbars = apply_filters( 'acf/fields/wysiwyg/toolbars', array() );
		$post_id  = 0;
		if ( $post ) {
			$post_id = intval( $post->ID );
		}


		// l10n
		$l10n = apply_filters( 'acf/input/admin_l10n', array(
			'core'       => array(
				'expand_details'   => __( "Expand Details", 'acf' ),
				'collapse_details' => __( "Collapse Details", 'acf' )
			),
			'validation' => array(
				'error' => __( "Validation Failed. One or more fields below are required.", 'acf' )
			)
		) );


		// options
		$o = array(
			'post_id'    => $post_id,
			'nonce'      => wp_create_nonce( 'acf_nonce' ),
			'admin_url'  => admin_url(),
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
			'wp_version' => $wp_version
		);


		// toolbars
		$t = array();

		if ( is_array( $toolbars ) ) {
			foreach ( $toolbars as $label => $rows ) {

				$label = sanitize_title( $label );
				$label = str_replace( '-', '_', $label );

				$t[ $label ] = array();

				if ( is_array( $rows ) ) {
					foreach ( $rows as $k => $v ) {

						$t[ $label ][ 'theme_advanced_buttons' . $k ] = implode( ',', $v );

					}
				}
			}
		}


		?>
        <script type="text/javascript">
            (function ($) {

                // vars
                acf.post_id = <?php echo is_numeric( $post_id ) ? $post_id : '"' . $post_id . '"'; ?>;
                acf.nonce = "<?php echo wp_create_nonce( 'acf_nonce' ); ?>";
                acf.admin_url = "<?php echo admin_url(); ?>";
                acf.ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
                acf.wp_version = "<?php echo $wp_version; ?>";


                // new vars
                acf.o = <?php echo json_encode( $o ); ?>;
                acf.l10n = <?php echo json_encode( $l10n ); ?>;
                acf.fields.wysiwyg.toolbars = <?php echo json_encode( $t ); ?>;

            })(jQuery);
        </script>
		<?php
	}

	function query_posts() {
		// vars
		$r = array(
			'next_page_exists' => 1,
			'html'             => ''
		);


		// options
		$options = array(
			'post_type'              => 'all',
			'taxonomy'               => 'all',
			'posts_per_page'         => 10,
			'paged'                  => 1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'post_status'            => 'any',
			'suppress_filters'       => false,
			's'                      => '',
			'lang'                   => false,
			'update_post_meta_cache' => false,
			'field_key'              => '',
			'nonce'                  => '',
			'ancestor'               => false,
		);

		$options = array_merge( $options, $_POST );


		// validate
		if ( ! wp_verify_nonce( $options['nonce'], 'acf_nonce' ) ) {
			die();
		}


		// WPML
		if ( $options['lang'] ) {
			global $sitepress;

			if ( ! empty( $sitepress ) ) {
				$sitepress->switch_lang( $options['lang'] );
			}
		}


		// convert types
		$options['post_type'] = explode( ',', $options['post_type'] );
		$options['taxonomy']  = explode( ',', $options['taxonomy'] );


		// load all post types by default
		if ( in_array( 'all', $options['post_type'] ) ) {
			$options['post_type'] = apply_filters( 'acf/get_post_types', array() );
		}


		// attachment doesn't work if it is the only item in an array???
		if ( is_array( $options['post_type'] ) && count( $options['post_type'] ) == 1 ) {
			$options['post_type'] = $options['post_type'][0];
		}


		// create tax queries
		if ( ! in_array( 'all', $options['taxonomy'] ) ) {
			// vars
			$taxonomies           = array();
			$options['tax_query'] = array();

			foreach ( $options['taxonomy'] as $v ) {

				// find term (find taxonomy!)
				// $term = array( 0 => $taxonomy, 1 => $term_id )
				$term = explode( ':', $v );


				// validate
				if ( ! is_array( $term ) || ! isset( $term[1] ) ) {
					continue;
				}


				// add to tax array
				$taxonomies[ $term[0] ][] = $term[1];

			}


			// now create the tax queries
			foreach ( $taxonomies as $k => $v ) {
				$options['tax_query'][] = array(
					'taxonomy' => $k,
					'field'    => 'id',
					'terms'    => $v,
				);
			}
		}

		unset( $options['taxonomy'] );


		// load field
		$field = array();
		if ( $options['ancestor'] ) {
			$ancestor = apply_filters( 'acf/load_field', array(), $options['ancestor'] );
			$field    = acf_get_child_field_from_parent_field( $options['field_key'], $ancestor );
		} else {
			$field = apply_filters( 'acf/load_field', array(), $options['field_key'] );
		}


		// get the post from which this field is rendered on
		$the_post = get_post( $options['post_id'] );


		// filters
//		$options = apply_filters( 'acf/fields/relationship/query', $options, $field, $the_post );
//		$options = apply_filters( 'acf/fields/relationship/query/name=' . $field['_name'], $options, $field, $the_post );
//		$options = apply_filters( 'acf/fields/relationship/query/key=' . $field['key'], $options, $field, $the_post );

		// query
		$wp_query = new WP_Query( $options );

		$r['debug'] = $options;

		// global
		global $post;


		// loop
		while ( $wp_query->have_posts() ) {

			$wp_query->the_post();
			$the_WP_Post = $wp_query->post;

			// get title
			//$title = $this->get_result( $post, $field, $the_post, $options );

			$imagesize = array( 50, 50 );
			// update html
			$r['html'] .= '<li><a href="' . get_permalink( $post->ID ) . '" data-post_id="' . $post->ID . '">' . $post->post_title . '   <span class="wcia_product_thumbnail">' . get_the_post_thumbnail( $post->ID, $imagesize ) . ' </span>
 <span class="relationship-item-info">Product</span>
<span class="acf-button-add"></span></a></li>';

		}


		// next page
		if ( (int) $options['paged'] >= $wp_query->max_num_pages ) {

			$r['next_page_exists'] = 0;

		}


		// reset
		wp_reset_postdata();


		// return JSON
		echo json_encode( $r );

		die();

	}


	function wcia_display_product_callback( $post ) {
		do_action( 'input_admin_head' );

		$field_id = get_post_meta( $post->ID, 'wcia_product', true );
		$args     = array(
			'post__in'  => $field_id, // ID of a page, post, or custom type
			'post_type' => 'product',
			'orderby'   => 'post__in'
		);

		if(!empty($field_id)){
			$field    = new WP_Query( $args );
		}
		?>
        <div id="acf-wcia_product" class="field field_type-relationship field_key-field_58d38c99102d5"
             data-field_name="wcia_product" data-field_key="field_58d38c99102d5" data-field_type="relationship">

            <div class="acf_relationship has-search " data-max="9999" data-s="" data-paged="1"
                 data-post_type="product" data-taxonomy="all" data-field_key="field_58d38c99102d5">

                <!--        <div class="acf_relationship--><?php ////echo $class; ?><!--"-->
                <!--            --><?php //foreach ( $attributes as $k => $v ): ?><!-- data--->
				<?php //echo $k; ?><!--="-->
				<?php //echo $v; ?><!--"--><?php //endforeach; ?>
                <!--        -->


                <!-- Hidden Blank default value -->
                <input type="hidden" name="<?php echo 'tolotra_hina_antsa_ianja_dally'; ?>" value=""/>


                <!-- Left List -->
                <div class="relationship_left">
                    <table class="widefat">
                        <thead>
                        <!--					--><?php //if ( in_array( 'search', $field['filters'] ) ): ?>
                        <tr>
                            <th>
                                <input class="relationship_search" placeholder="<?php _e( "Search...", 'acf' ); ?>"
                                       type="text" id="relationship_<?php echo 'search'; //$field['name']; ?>"
                                />
                            </th>
                        </tr>
                        <!--					--><?php //endif; ?>
						<?php if ( false ): ?>
                            <!--					--><?php //if ( in_array( 'post_type', $field['filters'] ) ): ?>
                            <tr>
                                <th>
									<?php

									// vars
									$choices = array(
										'all' => __( "Filter by post type", 'acf' )
									);


									if ( in_array( 'all', $field['post_type'] ) ) {
										$post_types = apply_filters( 'acf/get_post_types', array() );
										$choices    = array_merge( $choices, $post_types );
									} else {
										foreach ( $field['post_type'] as $post_type ) {
											$choices[ $post_type ] = $post_type;
										}
									}


									// create field
									do_action( 'acf/create_field', array(
										'type'    => 'select',
										'name'    => '',
										'class'   => 'select-post_type',
										'value'   => '',
										'choices' => $choices,
									) );

									?>
                                </th>
                            </tr>
						<?php endif; ?>
                        </thead>
                    </table>
                    <ul class="bl relationship_list">
                        <li class="load-more">
                            <div class="acf-loading"></div>
                        </li>
                    </ul>
                </div>
                <!-- /Left List -->

                <!-- Right List -->
                <div class="relationship_right">
                    <ul class="bl relationship_list">
						<?php

						if ( $field ) {
							while ( $field->have_posts() ) {
								$field->the_post();
								?>

                                <li><a href="<?php echo esc_url( get_permalink() ) ?>" class=""
                                       data-post_id="<?php the_ID() ?> ">
                                        <span class="wcia_product_thumbnail">
                                        <?php $image_id = get_post_thumbnail_id();
                                        $imagesize      = array( 50, 50 );
                                        the_post_thumbnail( $imagesize );

                                        ?></span>

                                        <span class="relationship-item-info">Product</span><?php esc_attr( the_title() ) ?>
                                        <span class="acf-button-remove"></span></a>
                                    <input type="hidden" name="tolotra_hina_antsa_ianja_dally[]"
                                           value="<?php the_ID() ?>"/></li>

								<?php

							}
						}
						?>

                    </ul>
                </div>
                <!-- / Right List -->

            </div>
        </div>
		<?php
	}


	function wcia_display_callback( $post ) {
		$image         = get_post_meta( $post->ID, 'wcia_annotation_image', true );
		$data          = get_post_meta( $post->ID, 'wcia_annotation_data', true );
		$data_object   = get_post_meta( $post->ID, 'wcia_annotation_data_object', true );
		$original_size = get_post_meta( $post->ID, 'wcia_annotation_canvas_size', true );
		?>
        <link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css">
        <div id="upload-area">
            <p>Image to annotate.</p>
            <div class="upload-fields">
                <input id="upload_image" type="text" size="36" name="upload_image" value="<?php echo $image; ?>"/>
                <input id="upload_image_button" type="button"
                       value="<?php _e( 'Choose image', 'image-annotator' ); ?>"/>
            </div>
        </div>
        <div id="work-area">
        <div id="wcia-toolbar">
            <ul class="buttons">
                <!--                <li class="select-button tool-button "><i class="fa fa-hand-pointer-o" aria-hidden="true"></i></li>-->
                <li class="remove-button-wcia tool-button-wcia"><i class="fa fa-times" aria-hidden="true"></i></li>
                <!--                <li class="circle-button tool-button"><i class="fa fa-circle-o" aria-hidden="true"></i></li>-->
                <!--                <li class="rectangle-button tool-button"><i class="fa fa-square-o" aria-hidden="true"></i></li>-->
                <!--                <li class="arrow-button tool-button"><i class="fa fa-long-arrow-right" aria-hidden="true"></i></li>-->
                <!--                <li class="text-button tool-button"><i class="fa fa-i-cursor" aria-hidden="true"></i></li>-->
                <!--                <li class="speech-bubble-button tool-button"><i class="fa fa-commenting-o" aria-hidden="true"></i></li>-->
                <li class="add-button tool-button-wcia active"><i class="fa fa-plus-circle" aria-hidden="true"></i></li>

            </ul>
        </div>
        <div id="canvas-area" class="wcia-canvas-area">
            <img src="<?php echo $image; ?>" alt="<?php _e( 'Annotator preview image', 'image-annotator' ); ?>"
                 id="wcia-preview-image">
        </div>
        <div id="raw-code">
            <p>Raw JSON for annotations</p>
            <textarea type="text" name="image_annotation" id="image_annotation_json"><?php echo $data; ?></textarea>
            <textarea type="text" name="image_annotation_object"
                      id="image_annotation_json_object"><?php echo $data_object; ?></textarea>
        </div>
        <input type="textarea" value="<?php echo $original_size; ?>" name="original_size" id="wcia-original-size">
		<?php
		wp_nonce_field( 'wcia_nonce_verify', 'wcia_nonce' );
	}

	/**
	 * Save meta box content.
	 *
	 * @param int $post_id Post ID
	 */
	function wcia_save_meta_box( $post_id ) {
		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['wcia_nonce'] ) ? $_POST['wcia_nonce'] : '';
		$nonce_action = 'wcia_nonce_verify';

		// Check if nonce is set.
		if ( ! isset( $nonce_name ) ) {
			return;
		}

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		update_metadata( 'post', $post_id, 'wcia_product', $_POST['tolotra_hina_antsa_ianja_dally'] );

		update_post_meta( $post_id, 'wcia_annotation_image', $_POST['upload_image'] );
		update_post_meta( $post_id, 'wcia_annotation_data', $_POST['image_annotation'] );
		update_post_meta( $post_id, 'wcia_annotation_data_object', $_POST['image_annotation_object'] );
		update_post_meta( $post_id, 'wcia_annotation_canvas_size', $_POST['original_size'] );

	}
}

new WCIA_Admin_Metaboxes();
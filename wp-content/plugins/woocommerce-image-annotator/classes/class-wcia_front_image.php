<?php

class wcia_front_image {

	public $wcia_options;

	public function __construct() {
		$this->wcia_options = get_option( 'wcia_options' );
		//Add the shortcode

		//Add the shortcode
		add_shortcode( 'wcia_image_all', array( $this, 'wcia_display_all_image_only' ) );
		add_shortcode( 'wcia_image', array( $this, 'wcia_fetch_image_product' ) );

		//Adds an empty JS object to the header to be used later on
		add_action( 'wp_head', array( $this, 'add_header_variable' ) );
		add_action( 'wcia_display_detailed_image_by_id', array( $this, 'wcia_display_detailed_image_by_id' ) );

		//Adds the styles and scripts to run things and make them look good
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts_styles' ) );

		//Adds the shortcode button in the TinyMCE editor
		add_action( 'init', array( $this, 'shortcode_button' ) );

		//Adds annotation JSON to the header so they can be loaded
		add_action( 'admin_print_scripts', array( $this, 'admin_scripts_styles' ) );
	}

	//This is a check for mobile that excludes iPads since the image still is relatively large on those

	protected function wcia_is_mobile() {
		static $is_mobile;

		if ( isset( $is_mobile ) ) {
			return $is_mobile;
		}

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$is_mobile = false;
		} elseif (
			strpos( $_SERVER['HTTP_USER_AGENT'], 'Android' ) !== false
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Silk/' ) !== false
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Kindle' ) !== false
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'BlackBerry' ) !== false
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mini' ) !== false
		) {
			$is_mobile = true;
		} elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Mobile' ) !== false && strpos( $_SERVER['HTTP_USER_AGENT'], 'iPad' ) == false ) {
			$is_mobile = true;
		} elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'iPad' ) !== false ) {
			$is_mobile = false;
		} else {
			$is_mobile = false;
		}

		return $is_mobile;
	}

	//shortcode, both
	public function wcia_fetch_image_product( $params ) {
		$picture_id = $params['id'];

		$array_attached_product = get_post_meta( $picture_id, 'wcia_product', true );
		$data_product_ids       = json_encode( $array_attached_product );

		ob_start();

		?>

        <div id="wcia_contend">
            <div class="image-product scrollable">
                <div class="wcia-summary-content">
					<?php
					//calling image information , in image annoator
					do_action( 'wcia_display_detailed_image_by_id', $picture_id );

					?>
                </div>
            </div>
            <div id="product-container" class="scrollable">
				<?php

				//calling product information in wcia (function  = wcia_get_product)
				do_action( 'wcia_get_product', $array_attached_product );

				?>
            </div>
            <div class="scrollbar_bg"></div>
        </div>

		<?php
		echo ob_get_clean();

		//exit();
	}

	// action image detailed one
	public function wcia_display_detailed_image_by_id( $product_id ) {
		$output = '';

		// Attributes
		$atts = shortcode_atts(
			array(
				'id' => '',
			),
			$atts,
			'wcia_image'
		);
		//Query args to get annotated image
		$args = array(
			'post_type'      => 'annotation',
			'posts_per_page' => 1,
			'p'              => $product_id
		);

		//print_r($args);
		$annotation_query = new WP_Query( $args );

		if ( $annotation_query->have_posts() ) {
			ob_start();
			while ( $annotation_query->have_posts() ) {
				$annotation_query->the_post();

				$data_object     = get_post_meta( get_the_ID(), 'wcia_annotation_data_object', true );
				$image           = get_post_meta( get_the_ID(), 'wcia_annotation_image', true );
				$data            = get_post_meta( get_the_ID(), 'wcia_annotation_data', true );
				$original_size   = get_post_meta( get_the_ID(), 'wcia_annotation_canvas_size', true );
				$annotation_data = json_decode( $data );
				$annotation_text = array();
				$is_mobile       = $this->wcia_is_mobile();
				$counter         = 0;


				if ( $is_mobile ) {
					$data = json_encode( $annotation_data );
				}

				if ( ! isset( $data_object ) || empty( $data_object ) ) {
					$data_object = "{}";
				}
				if ( ! isset( $data ) || empty( $data ) ) {
					$data = "{}";
				}

				?>

                <script>
                    wciaGeneratedImage["<?php echo get_the_ID(); ?>"] = <?php echo $data; ?>;
                </script>
                <script>
                    var currentWIPAObjectData = [];
                    currentWIPAObjectData[0] = <?php echo $data_object; ?>;
                </script>
                <div class="annotated-image-container">

                    <div class="annotated-image-wrapper" data-wcia="<?php echo get_the_ID(); ?>"
                         data-wcia-originalsize="<?php echo $original_size; ?>">
                        <img src="<?php echo $image; ?>" alt="<?php the_title(); ?>" class="annotated-image">
                        <!--                        <canvas id="live-canvas--->
						<?php //echo get_the_ID(); ?><!--" class="live-canvas">-->
                        <!--                        </canvas>-->
                    </div>
                </div>


				<?php

			} //end while have posts


			$post            = get_post( $product_id );
			$next_post       = get_next_post();
			$prev_post       = get_previous_post();
			$next_post_id    = ( $next_post != null ) ? $next_post->ID : '';
			$prev_post_id    = ( $prev_post != null ) ? $prev_post->ID : '';
			$next_post_title = ( $next_post != null ) ? $next_post->post_title : '';
			$prev_post_title = ( $prev_post != null ) ? $prev_post->post_title : '';
			$next_thumbnail  = ( $next_post != null ) ? get_the_post_thumbnail( $next_post->ID,
				'shop_thumbnail', '' ) : '';
			$prev_thumbnail  = ( $prev_post != null ) ? get_the_post_thumbnail( $prev_post->ID,
				'shop_thumbnail', '' ) : '';

			$dir = plugin_dir_url( __FILE__ );
			?>
            <script>
                var url = <?php echo "'" . "$dir../js/frontend.js'"; ?>;
                jQuery.getScript(url);
            </script>
            <script>
                var url = <?php echo "'" . "$dir../js/front/js/script.js'"; ?>;
                jQuery.getScript(url);
            </script>

            <div class="wcia_prev_data" data-wcia-prev-id="<?php echo $prev_post_id; ?>">
				<?php echo $prev_post_title; ?>
				<?php echo $prev_thumbnail; ?>
            </div>
            <div class="wcia_next_data" data-wcia-next-id="<?php echo $next_post_id; ?>">
				<?php echo $next_post_title; ?>
				<?php echo $next_thumbnail; ?>
            </div>
			<?php

			$output = ob_get_clean();
		}

		wp_enqueue_script( 'wcia-front-script' );

		wp_reset_postdata();

		echo $output;

	}

	//  shortcode all image only
	public function wcia_display_all_image_only( $atts ) {

		$output = '';

		// Attributes
		$atts = shortcode_atts(
			array(
				'id' => '',
			),
			$atts,
			'wcia_image'
		);

		//Query args to get annotated image
		$args = array(
			'post_type'      => 'annotation',
			'posts_per_page' => 20,
			'orderby'        => 'date',
			'order'          => 'ASC',
			//'p'                 => $atts['id']
		);

		$annotation_query = new WP_Query( $args );
		//echo ($annotation_query -> post_count);
		if ( $annotation_query->have_posts() ) {
			ob_start();

			$n = 0;
			echo '<div id="wcia_content">';
			while ( $annotation_query->have_posts() ) {
				//product id attached
				$annotation_query->the_post();

				$image           = get_post_meta( get_the_ID(), 'wcia_annotation_image', true );
				$data            = get_post_meta( get_the_ID(), 'wcia_annotation_data', true );
				$original_size   = get_post_meta( get_the_ID(), 'wcia_annotation_canvas_size', true );
				$annotation_data = json_decode( $data );
				$annotation_text = array();
				$is_mobile       = $this->wcia_is_mobile();
				$counter         = 0;
				$counter2        = 0;


				$array_attached_product = get_post_meta( get_the_ID(), 'wcia_product', true );
				//if($array_attached_product!= null)
				$data_product_ids = json_encode( $array_attached_product );
				if ( $is_mobile ) {
					$data = json_encode( $annotation_data );
				}
				//echo $n++;
				$shopping_tag          = $this->wcia_options['button_lable'];
				$shopping_tag_icon_url = plugins_url( '../img/front/pictures-icon-11.gif', __FILE__ );
				?>

                <div class="annotated-image-all-container">
                    <div class="annotated-image-marginer" style="background-image:url('<?php echo $image; ?>')"
                         alt="<?php the_title(); ?>">

                        <a href="#quick_view"
                           data-product-id='<?php echo $data_product_ids; ?>'
                           data-picture-id="<?php echo get_the_ID(); ?>"
                           class="wcia_quick_view qv_button annotated-image-all">
                            <div class="overlay">

                                <div class="text shopping_tag">
                                    <div class="shopping_tag-icon-container">
                                        <img
                                                src="<?php echo $shopping_tag_icon_url ?>"
                                                class="fs-icon fs-fa-shopping-tag invert"/></div>
									<?php echo $shopping_tag; ?> </div>
                            </div>
                            <!--                            <img  class=""/>-->
                            <!--<canvas id="live-canvas-<?php echo get_the_ID(); ?>" class="live-canvas"></canvas>-->
                        </a>
                    </div>
                </div>
				<?php

			}
			echo '</div>';
			$output = ob_get_clean();
		}

		wp_enqueue_script( 'wcia-front-script' );
		wp_reset_postdata();


		return $output;

	}

	public function add_scripts_styles() {
		wp_register_script( 'wcia-front-imagesloaded', plugins_url( '../lib/imagesLoaded/imagesloaded.pkgd.min.js', __FILE__ ), array( 'jquery' ) );
		wp_register_script( 'wcia-front-script', plugins_url( '../js/front/js/script.js', __FILE__ ), array(
			'jquery',
			'wcia-front-imagesloaded'
		) );
		wp_enqueue_style( 'wcia-front-style', plugins_url( '../css/front/css/style.css', __FILE__ ) );
	}

	public function add_header_variable() {
		echo '<script type="text/javascript"> var wciaGeneratedImage = {}; </script>';
	}

	public function shortcode_button() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( get_user_option( 'rich_editing' ) == 'true' ) {
			add_filter( 'mce_external_plugins', array( $this, 'add_plugin' ) );
			add_filter( 'mce_buttons', array( $this, 'register_button' ) );
		}
	}

	public function register_button( $buttons ) {
		array_push( $buttons, "|", "annotate" );

		return $buttons;
	}

	public function add_plugin( $plugin_array ) {
		$plugin_array['annotate'] = plugins_url( '../js/front/js/tinymce.js', __FILE__ );

		return $plugin_array;
	}

	public function get_annotations() {
		$annotations_array = array();
		$args              = array(
			'posts_per_page' => 500,
			'post_type'      => 'annotation'
		);

		$annotations = get_posts( $args );

		if ( ! empty( $annotations ) ) {
			foreach ( $annotations as $annotation ) {

				$annotations_array[] = array(
					'value' => $annotation->ID,
					'text'  => $annotation->post_title
				);
			}
		}

		return $annotations_array;

	}

	public function admin_scripts_styles() {

		$annotations_array = $this->get_annotations();

		echo "<script type='text/javascript'>\n";
		echo 'var annotations = ' . wp_json_encode( $annotations_array ) . ';';
		echo "\n</script>";
	}

}

new wcia_front_image();
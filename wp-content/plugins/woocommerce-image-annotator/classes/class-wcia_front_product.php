<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class wcia_front_product {

	public $wcia_plugin_dir_url;
	public $wcia_options;
	public $wcia_style;

	function __construct( $wcia_plugin_dir_url ) {

		$this->wcia_plugin_dir_url = $wcia_plugin_dir_url;
		$this->wcia_options        = get_option( 'wcia_options' );
		$this->wcia_style          = get_option( 'wcia_style' );

		add_action( 'wp_enqueue_scripts', array( $this, 'wcia_load_assets' ) );
		//add_action( 'woocommerce_after_shop_loop_item', array( $this, 'wcia_add_button' ) );
		//add_action( 'woocommerce_after_shop_loop', array( $this, 'wcia_remodel_model' ) );
		//adding ajax actions for non logged users
		add_action( 'admin_init', array( $this, 'add_ajax_actions' ) );

		add_action( 'wcia_get_product', array( $this, 'wcia_get_product' ) );
		//add_action( 'wp_ajax_nopriv_wcia_get_product', array($this,'wcia_get_product') );
		//	add_action( 'wcia_remodel_model', array( $this, 'wcia_remodel_model' ) ); //placeholder popup
		add_action( 'wp_footer', array( $this, 'wcia_remodel_model' ) ); //placeholder popup

		add_action( 'wcia_show_product_sale_flash', 'woocommerce_show_product_sale_flash' );
		add_action( 'wcia_show_product_images', array( $this, 'wcia_woocommerce_show_product_images' ) );

		add_action( 'wcia_product_data', 'woocommerce_template_single_title' );
		add_action( 'wcia_product_data', 'woocommerce_template_single_rating' );
		add_action( 'wcia_product_data', 'woocommerce_template_single_price' );
		add_action( 'wcia_product_data', 'woocommerce_template_single_excerpt' );
//       add_action( 'wcia_product_data', 'woocommerce_template_single_add_to_cart');
		//add_action( 'wcia_product_data', 'woocommerce_template_single_meta' );
		//add_action( 'wcia_product_data', 'woocommerce_template_single_meta' );
//
	}

	function add_ajax_actions() {
		add_action( 'wp_ajax_wcia_get_image_products', array( $this, 'wcia_get_image_product' ) );
		add_action( 'wp_ajax_nopriv_wcia_get_image_products', array( $this, 'wcia_get_image_product' ) );
		//add_action( 'wp_ajax_wcia_get_product', array( $this, 'wcia_get_product' ) );
	}

	public function wcia_get_product( $array_attached_product ) {

		global $woocommerce;
		global $post;

		$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$lightbox_en = get_option( 'woocommerce_enable_lightbox' ) == 'yes' ? true : false;

//		echo '<pre>';
//		print_r( $array_attached_product );
//		echo '</pre>';

		//$array_attached_product = $_POST['product_id'];
		$order = 0;
		echo '<div class="product-left-container">';
		foreach ( $array_attached_product as $product_id ) {


			if ( intval( $product_id ) ) {
				$args        = array(
					'post_type'      => 'product',
					'posts_per_page' => 20,
					'p'              => $product_id
				);
				$all_product = new WP_Query( $args );
				//$all_product = wp( 'p=' . $product_id . '&post_type=product' );
				ob_start();


				while ( $all_product->have_posts() ) {
					$all_product->the_post(); ?>
                    <div class="product left" data-order="<?php $order ++;
					echo $order ?>">
                        <script>
                            var url = <?php echo "'" . "$this->wcia_plugin_dir_url/js/prettyPhoto.init.js'"; ?>;
                            jQuery.getScript(url);
                            var wc_add_to_cart_variation_params = {"ajax_url": "\/wp-admin\/admin-ajax.php"};
                            jQuery.getScript("<?php echo $woocommerce->plugin_url(); ?>/assets/js/frontend/add-to-cart-variation.min.js");
                        </script>


                        <div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>"
                             id="product-<?php the_ID(); ?>" <?php post_class( 'product' ); ?> >
							<?php do_action( 'wcia_show_product_sale_flash' ); ?>
                            <div class="img-product-popup">
                                <a class="wc-url-product" href="<?php echo esc_url( get_permalink() ); ?>"
                                   title="<?php esc_attr( the_title() ); ?>">
									<?php the_post_thumbnail(); ?>
                                    <!--	                                --><?php //do_action( 'wcia_show_product_images' );
									?>
                                </a>

                                <div>
                                    <div class="product-details">
                                        <div class="summary-content">
                                            <a href="<?php echo esc_url( get_permalink() ); ?>"
                                               title="<?php esc_attr( the_title() ); ?>">
												<?php do_action( 'wcia_product_data' ); ?>
                                            </a>

                                            <div class="wcia-footer">
                                                <div class="stats">
													<?php
													echo $this->theme_add_to_cart();
													?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="scrollbar_bg"></div>
                                </div>
                            </div>
                        </div>
                    </div>

					<?php
				}

			}
		} //end foreach attached_product_id
		echo '</div>';

		?>

        </div>

		<?php

		echo ob_get_clean();

		//exit();
	}


	//first function called by ajax, my custom function
	public function wcia_get_image_product() {
		$picture_id = $_POST['picture_id'];
		echo do_shortcode( '[wcia_image id=' . $picture_id . ']' );
	}


	public function wcia_woocommerce_show_product_images() {

		global $post, $product, $woocommerce;

		?>
        <div class="images">
			<?php

			if ( has_post_thumbnail() ) {
				$attachment_count = count( $product->get_gallery_attachment_ids() );
				$gallery          = $attachment_count > 0 ? '[product-gallery]' : '';
				$props            = wc_get_product_attachment_props( get_post_thumbnail_id(), $post );
				$image            = get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), array(
					'title' => $props['title'],
					'alt'   => $props['alt'],
				) );
				echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto' . $gallery . '">%s</a>', $props['url'], $props['caption'], $image ), $post->ID );
			} else {
				echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="%s" />', wc_placeholder_img_src(), __( 'Placeholder', 'woocommerce' ) ), $post->ID );
			}


			$attachment_ids = $product->get_gallery_attachment_ids();
			if ( $attachment_ids ) :
				$loop = 0;
				$columns    = apply_filters( 'woocommerce_product_thumbnails_columns', 3 );
				?>
                <div class="thumbnails <?php echo 'columns-' . $columns; ?>"><?php
					foreach ( $attachment_ids as $attachment_id ) {
						$classes = array( 'thumbnail' );
						if ( $loop === 0 || $loop % $columns === 0 ) {
							$classes[] = 'first';
						}
						if ( ( $loop + 1 ) % $columns === 0 ) {
							$classes[] = 'last';
						}
						$image_link = wp_get_attachment_url( $attachment_id );
						if ( ! $image_link ) {
							continue;
						}
						$image_title   = esc_attr( get_the_title( $attachment_id ) );
						$image_caption = esc_attr( get_post_field( 'post_excerpt', $attachment_id ) );
						$image         = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ), 0, $attr = array(
							'title' => $image_title,
							'alt'   => $image_title
						) );
						$image_class   = esc_attr( implode( ' ', $classes ) );
						echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', sprintf( '<a href="%s" class="%s" title="%s" >%s</a>', $image_link, $image_class, $image_caption, $image ), $attachment_id, $post->ID, $image_class );
						$loop ++;
					}
					?>

                </div>
			<?php endif; ?>
        </div>
		<?php
	}


	public function wcia_load_assets() {

		wp_enqueue_style( 'wcia_remodal_default_css', $this->wcia_plugin_dir_url . 'css/style.css' );
		wp_register_script( 'wcia_frontend_js', $this->wcia_plugin_dir_url . 'js/frontend.js', array( 'jquery' ), '1.0', true );
		$frontend_data = array(
			'wcia_nonce'          => wp_create_nonce( 'wcia_nonce' ),
			'ajaxurl'             => admin_url( 'admin-ajax.php' ),
			'wcia_plugin_dir_url' => $this->wcia_plugin_dir_url
		);

		wp_localize_script( 'wcia_frontend_js', 'wcia_frontend_obj', $frontend_data );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wcia_frontend_js' );
		wp_register_script( 'wcia_remodal_js', $this->wcia_plugin_dir_url . 'js/remodal.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'wcia_remodal_js' );

		global $woocommerce;

		$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$lightbox_en = get_option( 'woocommerce_enable_lightbox' ) == 'yes' ? true : false;

		if ( $lightbox_en ) {
			wp_enqueue_script( 'prettyPhoto', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), '3.1.6', true );
			wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce->plugin_url() . '/assets/css/prettyPhoto.css' );
		}
		wp_enqueue_script( 'thickbox' );


		$custom_css = '
	    .remodal .remodal-close{
	    	color:' . $this->wcia_style['close_btn'] . ';
	    }
	    .remodal .remodal-close:hover{
	    	background-color:' . $this->wcia_style['close_btn_bg'] . ';
	    }
	    .woocommerce .remodal{
	    	background-color:' . $this->wcia_style['modal_bg'] . ';
	    }
	    .wcia_prev h4,.wcia_next h4{
	    	color :' . $this->wcia_style['navigation_txt'] . ';
	    }
	    .wcia_prev,.wcia_next{
	    	background :' . $this->wcia_style['navigation_bg'] . ';
	    }
        .woocommerce a.wcia_quick_view{
            background-color: ' . $this->wcia_style['close_btn'] . ' ;
        }';
		wp_add_inline_style( 'wcia_remodal_default_css', $custom_css );


	}


	public function wcia_remodel_model() {

		echo '<div class="remodal" id="wcia_remodal" data-remodal-id="wcia_remodal" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc">
		  <button data-remodal-action="close" class="remodal-close" aria-label="Close"></button>
		    <div id = "wcia_contend"></div>
		</div>';

	}


	public function wcia_add_button() {

		global $product;
		echo '<a data-product-id="' . $product->id . '"class="wcia_quick_view qv_button" >
        <span>' . $this->wcia_options['button_lable'] . '</span></a>';
	}

	public function theme_add_to_cart() {

		global $product;

		if ( $product ) {
			$args     = array();
			$defaults = array(
				'quantity' => 1,
				'class'    => implode( ' ', array_filter( array(
					'button',
					'product_type_' . $product->product_type,
					$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
					$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
				) ) ),
			);

			$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );


			echo apply_filters( 'woocommerce_loop_add_to_cart_link',
				sprintf( '<a class="%5$s add_to_cart_txt  product_type_simple add_to_cart_button ajax_add_to_cart" href="%1$s" data-quantity="%2$s" data-product_id="%3$s" data-product_sku="%4$s">Add to cart</a><a rel="nofollow" href="%1$s" data-quantity="%2$s" data-product_id="%3$s" data-product_sku="%4$s" class="%5$s btn btn-just-icon btn-simple btn-default  product_type_simple add_to_cart_button ajax_add_to_cart wcia-ajax_add_to_cart" title="%6$s"><i rel="tooltip" data-original-title="%6$s" class="fa fa-cart-plus"></i></a>',
					esc_url( $product->add_to_cart_url() ),
					esc_attr( isset( $quantity ) ? $quantity : 1 ),
					esc_attr( $product->id ),
					esc_attr( $product->get_sku() ),
					esc_attr( isset( $class ) ? $class : 'button' ),
					esc_html( $product->add_to_cart_text() )
				),
				$product );
			//wc_get_template( 'classes/woocommerce/add-to-cart.php', $args );
		}
	}


}

?>
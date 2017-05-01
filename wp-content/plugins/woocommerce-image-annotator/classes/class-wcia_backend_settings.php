<?php 

if (!defined( 'ABSPATH')) exit;

class wcia_backend_settings{

	public $wcia_plugin_dir_url;
	public $wcia_options;
    public $wcia_style;

	function __construct($wcia_plugin_dir_url){

		$this->wcia_plugin_dir_url = $wcia_plugin_dir_url;

		add_action( 'admin_menu', array($this,'wcia_admin_menu' ));

		add_action('admin_notices', array($this, 'wpcqv_admin_notice' ) );
		add_action('admin_init', array( $this, 'wpcqv_view_ignore_notice') );


	}



public function wcia_admin_menu() {

	add_options_page( 'View Annotation Options', 'View Annotation', 'manage_options', 'woocommerce-image-annotation', array($this,'wcia_quick_view_options') );
}

function wcia_quick_view_options() {

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker-alpha', $this->wcia_plugin_dir_url . 'js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), '1.2.2');

	wp_enqueue_style( 'wcia_admin_style',  $this->wcia_plugin_dir_url . 'css/admin.css');
	wp_enqueue_script('wcia_admin_js',$this->wcia_plugin_dir_url . 'js/admin.js',array( 'jquery', 'wp-color-picker' ),'', true);

	if ( !current_user_can( 'activate_plugins' ) )  {
		wp_die( _e( 'You do not have sufficient permissions to access this page.','woo-image-annotation' ) );
	}
	?>
	<div style="display: none" class="wcia_warn_msg">
    <img  src="<?php echo $this->wcia_plugin_dir_url  .'/img/warn.png'; ?>"> <b>Woocommerce Image Annotator Lite</b>
     is a fully functional but limited version of <b><a href="https://ciphercoin.com/downloads/woocommerce-image-annotation/" target="_blank">Woocommerce Image Annotator Pro</a></b>. Consider upgrading to get access to all premium features and premium support.
    </div>
    <?php 
	if(isset($_POST['button_lable'])){
	      
	    $nonce = $_REQUEST['_wpnonce'];
	    if ( wp_verify_nonce( $nonce, 'wcia-admn-nonce' ) ) {
	

	    	$data = array(
				'enable_quick_view' => (isset($_POST['enable_quick_view'])?'1':'0'),
				'enable_mobile'     => (isset($_POST['enable_mobile'])?'1':'0'),
				'button_lable'      => esc_sql($_POST['button_lable'])
			);
			update_option('wcia_options', $data);

			$data = array(
				'modal_bg'    		=>  esc_sql($_POST['modal_bg']),
				'close_btn'    		=>  esc_sql($_POST['close_btn']),
				'close_btn_bg' 		=>  esc_sql($_POST['close_btn_bg']),
				'navigation_bg'		=>  esc_sql($_POST['navigation_bg']),
                'navigation_txt'	=>  esc_sql($_POST['navigation_txt'])
				);
			update_option( 'wcia_style', $data );
	    } 
    } 
    $this->wcia_options = get_option('wcia_options');
  	$this->wcia_style   = get_option('wcia_style');

  	$wcia_admn_nonce = wp_create_nonce( 'wcia-admn-nonce' );
	?>
	<h2><?php _e('General Options','woo-image-annotation'); ?></h2>
	<form action='options-general.php?page=woocommerce-image-annotation&_wpnonce=<?php echo $wcia_admn_nonce; ?>' method='post'>
	<table class="form-table">
	<tr valign='top'>
	<th><lable><?php _e('Enable View Annotation','woo-image-annotation'); ?></lable></th>
	<td scop='row'>
	<input name="enable_quick_view" type="checkbox" 
		<?php echo ($this->wcia_options['enable_quick_view']==1)? 'checked="checked"':  ''; ?> />
	</td>
	</tr>
	<tr valign='top'>
	<th><lable><?php _e('Enable View Annotation on mobile','woo-image-annotation'); ?></lable></th>
	<td scop='row'>
	<input name="enable_mobile" type="checkbox"
		<?php echo ($this->wcia_options['enable_mobile']==1)? 'checked="checked"':  ''; ?>  />
	</td>
	</tr>
	<tr valign='top'>
	<th><lable><?php _e('View Annotation Button Label','woo-image-annotation'); ?></lable></th>
	<td scop='row'>
	<input name="button_lable" type="text" value="<?php echo $this->wcia_options['button_lable']; ?>" />
	</td>
	</tr>
	 
	</table>

    <h2><?php _e('Style Options','woo-image-annotation');?></h2>
	<table class='form-table'>
	<tbody>
	<tr valign='top'>
	<th><lable><?php _e('Modal Window Background Color','woo-image-annotation'); ?></lable></th>
	<td scop='row'>
	<input type ="text" name="modal_bg" value="<?php echo $this->wcia_style['modal_bg'];?>" class="wcia-color-picker" data-default-color="#fff" />
	</td>
	</tr>
	<tr valign='top'>
	<th><lable><?php _e('Closing Button Color','woo-image-annotation'); ?></lable></th>
	<td scop='row'>
	<input type ="text" name="close_btn" value="<?php echo $this->wcia_style['close_btn']; ?>" class="wcia-color-picker" data-default-color="#95979c" />
	</td>
	</tr>
	<tr valign='top'>
	<th><lable><?php _e('Closing Button Hover Background Color','woo-image-annotation'); ?></lable></th>
	<td scop='row'>
	<input type ="text" name="close_btn_bg" value="<?php echo $this->wcia_style['close_btn_bg']; ?>" class="wcia-color-picker" data-default-color="#4C6298" />
	</td>
	</tr>
	<tr valign='top'>
	<th><lable><?php _e('Navigation Box Background Color','woo-image-annotation'); ?></lable></th>
	<td scop='row'>
	<input type ="text" name="navigation_bg" data-alpha="true" value="<?php echo $this->wcia_style['navigation_bg']; ?>" class="wcia-color-picker" data-default-color="rgba(255, 255, 255, 0.2)" />
	</td>
	</tr>
	<tr valign='top'>
	<th><lable><?php _e('Navigation Box Text Color','woo-image-annotation'); ?></lable></th>
	<td scop='row'>
	<input type ="text" name="navigation_txt" value="<?php echo $this->wcia_style['navigation_txt']; ?>" class="wcia-color-picker" data-default-color="#fff" />
	</td>
	</tr>
	</tbody>
	</table>
		<input type ="submit" class="button-primary" value="Save Changes">
	</form>

	<p Style="float:left; display: none"> If you like <strong>Woocommerce Image Annotator</strong> please leave us a <a href="https://wordpress.org/support/view/plugin-reviews/woo-image-annotation" target="_blank" data-rated="Thanks :)">★★★★★</a> rating. A huge thank you from cipherCoin in advance!</p>
	<div class="clear"></div>
	<div style="display: none" class="wcia_warn_msg">
    <img src="<?php echo $this->wcia_plugin_dir_url  .'/img/warn.png'; ?>"> <b>Woocommerce Image Annotator Lite</b>
     is a fully functional but limited version of <b><a href="https://ciphercoin.com/downloads/woocommerce-image-annotation/" target="_blank">Woocommerce Image Annotator Pro</a></b>. Consider upgrading to get access to all premium features and premium support.
    </div>
	<?php

}




	public function mobile_detect(){

		$useragent=$_SERVER['HTTP_USER_AGENT'];
		if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){

			return true;

		}else{

			return false;
		}
	}


	/* Display a notice that can be dismissed */



public function wpcqv_admin_notice() {

	$install_date = get_option( 'wpcqv_view_install_date', '');
	$install_date = date_create( $install_date );
	$date_now	  = date_create( date('Y-m-d G:i:s') );
	$date_diff    = date_diff( $install_date, $date_now );

	if ( $date_diff->format("%d") < 7 ) {
		
		return false;
	}

    global $current_user ;
    $user_id = $current_user->ID;
 
    if ( ! get_user_meta($user_id, 'wpcqv_view_ignore_notice' ) ) {

        echo '<div style="display: none" class="updated"><p>';

        printf(__('Awesome, you\'ve been using <a href="options-general.php?page=woocommerce-image-annotation">WooCommerce View Annotation</a> for more than 1 week. May we ask you to give it a 5-star rating on WordPress? | <a href="%2$s" target="_blank">Ok, you deserved it</a> | <a href="%1$s">I alredy did</a> | <a href="%1$s">No, not good enough</a>'), '?wpcqv_view_ignore_notice=0','https://wordpress.org/plugins/wcia-image-annotation/');
        echo "</p></div>";
    }
}

public function wpcqv_view_ignore_notice() {
    global $current_user;
    $user_id = $current_user->ID;
 
    if ( isset($_GET['wpcqv_view_ignore_notice']) && '0' == $_GET['wpcqv_view_ignore_notice'] ) {

        add_user_meta($user_id, 'wpcqv_view_ignore_notice', 'true', true);
    }
}

 
}
?>
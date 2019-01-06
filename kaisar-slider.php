<?php 
/**
* Plugin Name: Kaisar Slider
* Plugin URI: https://agenkaisar.online/
* Description: Klasemen Slider
* Version: 1.0
* Author: Odhier
* Author URI: https://agenkaisar.online/
*/
/*
* Creating a function to create our CPT
*/
 
class Kaisar_Slider {
	
	
	public function __construct() {
		
		add_action( 'init', array( &$this, 'init' ) );
		
		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
		}
	}
	
	
	/** Frontend methods ******************************************************/
	/** 
	* Hooks Shortcode
	*/
	public function get_shortcode($atts){

		wp_enqueue_style( 'kaisar-slider',  plugin_dir_url( __FILE__ ).'css/style.css', array(), '1.0', null );
		wp_enqueue_script( 'slick',  plugin_dir_url( __FILE__ ).'js/slick.js', array('jquery'), '1.0', null );

		$a = shortcode_atts( array(
			'id' => '',
	    ), $atts );
	    $anim_slider = get_post_meta( $a['id'], '_anim_slider', true );
		$height = get_post_meta($a['id'], '_height_slider', true );
		$height = (!empty($height))?$height:'220';

        $args = array(
            'post_parent' => $a['id'],
            'post_type'   => 'attachment', 
            'numberposts' => -1,
            'post_status' => 'inherit' 
		);
		?>
		<div class="kaisar-slideshow anim_style<?php echo $anim_slider;?>" style="<?php echo ($anim_slider==1)?'height:'.$height.'':'';?>px">
			<?php include('templates/style-'.$anim_slider).'.php'?>		
		</div>

		<script type="text/javascript">
		jQuery(document).ready(function($) {
		<?php if($anim_slider==2){ ?>
			$('.kslide-img').slick({
		        slidesToShow: 4,
		        slidesToScroll: 1,
		        autoplay: true,
		        autoplaySpeed: 1500,
		        arrows: false,
		        dots: false,
		        pauseOnHover: false,
		        responsive: [{
		            breakpoint: 768,
		            settings: {
		                slidesToShow: 4
		            }
		        }, {
		            breakpoint: 520,
		            settings: {
		                slidesToShow: 3
		            }
		        }]
		    });
		<?php } ?>
			});
		</script>
		<?php

	}
	
	/**
	 * Register the custom post type
	 */
	public function init() {
	    register_post_type( 'kaisar_slider', array( 'public' => true, 'label' => 'Kaisar Slider','supports' => array( 'title') ) );

		add_shortcode( 'kaisar-slider', array( &$this, 'get_shortcode' ));
	}
	
	
	/** Admin methods ******************************************************/

    /**
     * Load all scripts
     */
    public function load_admin_things() {
		wp_enqueue_style('thickbox');
		wp_enqueue_style( 'kaisar-slider',  plugin_dir_url( __FILE__ ).'css/style.css', array(), '1.0', null );
		wp_enqueue_style( 'kaisar-slider-admin',  plugin_dir_url( __FILE__ ).'css/admin-style.css', array(), '1.0', null );
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');

		wp_enqueue_script( 'slick',  plugin_dir_url( __FILE__ ).'js/slick.js', array('jquery'), '1.0', null );
	}
	
	/**
	 * Initialize the admin, adding actions to properly display and handle 
	 * the Book custom post type add/edit page
	 */
	public function admin_init() {
        global $pagenow;
        if ( $pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'edit.php' ) {
			add_action( 'admin_enqueue_scripts', array(&$this,'load_admin_things') );

			add_action( 'add_meta_boxes', array( &$this, 'meta_boxes' ) );
            add_filter( 'enter_title_here', array( &$this, 'enter_title_here' ), 1, 2 );

			add_action( 'add_meta_boxes', array( &$this, 'meta_settings_boxes' ) );
			add_action( 'add_meta_boxes', array( &$this, 'meta_shortcode_boxes' ) );
			add_action( 'save_post', array( &$this, 'meta_boxes_save' ), 1, 2 );
		}
	}
	
	
	/**
	 * Save meta boxes
	 * 
	 * Runs when a post is saved and does an action which the write panel save scripts can hook into.
	 */
	public function meta_boxes_save( $post_id, $post ) {
        
		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( is_int( wp_is_post_revision( $post ) ) ) return;
		if ( is_int( wp_is_post_autosave( $post ) ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( $post->post_type != 'kaisar_slider' ) return;
		$this->process_slider_meta( $post_id, $post );
	}
	
	/**
	* Add Animation Field Slider
	*/
	public function meta_settings_boxes(){
		add_meta_box( 'setting-slider', __('Settings'), array( &$this, 'slider_settings_meta'), 'kaisar_slider', 'side', 'high');
	}
	
	/**
	 * Function for processing and storing all data.
	 */
	private function process_slider_meta( $post_id, $post ) {
		update_post_meta( $post_id, '_image_id', $_POST['upload_image_id'] );
  		update_post_meta( $post_id, '_anim_slider', $_POST['anim_slider'] );
  		update_post_meta( $post_id, '_height_slider', $_POST['height'] );
	}
	
	
	/**
	 * Set a more appropriate placeholder text for the New slider title field
	 */
	public function enter_title_here( $text, $post ) {
		if ( $post->post_type == 'kaisar_slider' ) return __( 'Slider Title' );
		return $text;
	}
	
	
	/**
	 * Add and remove meta boxes from the edit page
	 */
	public function meta_boxes() {
		add_meta_box( 'slider-image', __( 'Slider Image' ), array( &$this, 'slider_image_meta_box' ), 'kaisar_slider', 'normal', 'high' );
    }
    /**
	 * Add and remove meta boxes from the edit page
	 */
    public function meta_shortcode_boxes() {
		add_meta_box( 'slider-shortcode', __( 'Slider Shortcode' ), array( &$this, 'slider_shortcode_meta_box' ), 'kaisar_slider', 'normal', 'default' );
    }
    
    /**
    * Slider Anim Box
    */
    public function slider_settings_meta(){
    	global $post;

		$anim_slider = get_post_meta( $post->ID, '_anim_slider', true );
		$height_slider = get_post_meta( $post->ID, '_height_slider', true );
    	wp_nonce_field( plugin_basename( __FILE__ ), 'slider_box_setting' );
		?>
    	<table>
    		<tr>
    			<td>Animation Style:</td>
    		</tr>
    		<tr>
    			<td>
    				<label for="anim_slider"></label>
					<select class="anim_slider" id="anim_slider" name="anim_slider">
						<option <?php echo (!empty($anim_slider) && $anim_slider==1)?'selected ':''?>value="1">Style 1</option>
						<option <?php echo (!empty($anim_slider) && $anim_slider==2)?'selected ':''?>value="2">Style 2</option>
					</select>
				</td>
				
			</tr>
			<tr class='height <?php echo ($anim_slider == 2)?"hidden":""?>'>
				<td>Height Images:</td>
    		</tr>
    		<tr class='height <?php echo ($anim_slider == 2)?"hidden":""?>' >
    			<td>
    				<label for="height"></label>
					<input type="text" name="height" id="height" value="<?php echo (!empty($height_slider))?$height_slider:'';?>">px
				</td>
			</tr>
		</table>
    	<?php
    }

	/**
     * Display Shortcode Meta Box
     */
    public function slider_shortcode_meta_box(){
        global $post;
        $current_id = $post->ID;
        ?>
        <input type="text" style="width:100%;text-align:center;font-size:18pt;" readonly id="slider-shortcode" value="[kaisar-slider id='<?php echo ($current_id)?$current_id:''; ?>']" />
        
        <?php
    }
     
	/**
	 * Display the image meta box
	 */
	public function slider_image_meta_box() {
		global $post;
		$image_src = '';
		$image_id = get_post_meta( $post->ID, '_image_id', true );
		$image_src = wp_get_attachment_url( $image_id );
		$anim_slider = get_post_meta( $post->ID, '_anim_slider', true );
		$height = get_post_meta( $post->ID, '_height_slider', true );
		$height = (!empty($height))?$height:'220';

        $args = array(
            'post_parent' => $post->ID,
            'post_type'   => 'attachment', 
            'numberposts' => -1,
            'post_status' => 'inherit' 
		);
		?>

		<div class="kaisar-slideshow anim_style<?php echo $anim_slider;?>" style="<?php echo ($anim_slider==1)?'height:'.$height.'':'';?>px">
			<?php include('templates/style-'.$anim_slider).'.php'?>		
		</div>
		<input type="hidden" name="upload_image_id" id="upload_image_id" value="<?php echo $children->guid; ?>" />
		<p>
			<a title="<?php esc_attr_e( 'Set slider image' ) ?>" href="#" id="set-slider-image"><?php _e( 'Set slider image' ) ?></a>
			
		</p>
		
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			//Require post title when adding/editing Project Summaries
			$( 'body' ).on( 'submit.edit-post', '#post', function () {

				// If the title isn't set
				if ( $( "#title" ).val().replace( / /g, '' ).length === 0 ) {

					// Show the alert
					window.alert( 'A title is required.' );

					// Hide the spinner
					$( '#major-publishing-actions .spinner' ).hide();

					// The buttons get "disabled" added to them on submit. Remove that class.
					$( '#major-publishing-actions' ).find( ':button, :submit, a.submitdelete, #post-preview' ).removeClass( 'disabled' );

					// Focus on the title field.
					$( "#title" ).focus();

					return false;
				}
			});
			
			window.send_to_editor_default = window.send_to_editor;
	
			$('#set-slider-image').click(function(){
				
				window.send_to_editor = window.attach_image;
				tb_show('', 'media-upload.php?post_id=<?php echo $post->ID ?>&amp;type=image&amp;TB_iframe=true');
				
				return false;
			});
			
			$('#remove-slider-image').click(function() {
				
				$('#upload_image_id').val('');
				$('img').attr('src', '');
				$(this).hide();
				
				return false;
			});
			
			window.attach_image = function(html) {
				
				$('body').append('<div id="temp_image">' + html + '</div>');
					
				var img = $('#temp_image').find('img');
				
				imgurl   = img.attr('src');
				imgclass = img.attr('class');
				imgid    = parseInt(imgclass.replace(/\D/g, ''), 10);
	
				$('#upload_image_id').val(imgid);
				$('#remove-slider-image').show();
	
				$('img#slider_image').attr('src', imgurl);
				try{tb_remove();}catch(e){};
				$('#temp_image').remove();
				
				window.send_to_editor = window.send_to_editor_default;
				
			}
			$('#anim_slider').change(function(){
				var valueSelected = this.value;
				if(valueSelected == 1){
					$('.height').removeClass('hidden');
				}else{
					$('.height').addClass('hidden');
				}
			})
			<?php if($anim_slider==2){ ?>
			$('.kslide-img').slick({
		        slidesToShow: 4,
		        slidesToScroll: 1,
		        autoplay: true,
		        autoplaySpeed: 1500,
		        arrows: false,
		        dots: false,
		        pauseOnHover: false,
		        responsive: [{
		            breakpoint: 768,
		            settings: {
		                slidesToShow: 4
		            }
		        }, {
		            breakpoint: 520,
		            settings: {
		                slidesToShow: 3
		            }
		        }]
		    });
		<?php } ?>
	
		});
		</script>
		<?php
	}
}

$GLOBALS['Kaisar_Slider'] = new Kaisar_Slider();

?>
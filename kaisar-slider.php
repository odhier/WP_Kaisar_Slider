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
	 * Register the custom post type
	 */
	public function init() {
	    register_post_type( 'kaisar_slider', array( 'public' => true, 'label' => 'Kaisar Slider','supports' => array( 'title' ) ) );
	}
	
	
	/** Admin methods ******************************************************/
    
    /**
     * Load all scripts
     */
    public function load_admin_things() {
		wp_enqueue_style('thickbox');
		wp_enqueue_style( 'kaisar-slider',  plugin_dir_url( __FILE__ ).'css/style.css', array(), '1.0', null );
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
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
	 * Function for processing and storing all data.
	 */
	private function process_slider_meta( $post_id, $post ) {
		update_post_meta( $post_id, '_image_id', $_POST['upload_image_id'] );
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
     * Display Shortcode Meta Box
     */
    public function slider_shortcode_meta_box(){
        global $post;
        $current_id = $post->ID;
        ?>
        <input type="text" style="width:100%;text-align:center;font-size:18pt;" readonly id="slider-shortcode" value="[kaisar-slider id='<?php echo ($current_id)?$current_id:''; ?>']" />
        
        <?php
   
		// $image_id = get_post_meta( $post->ID, '_image_id', true );
        // $image_src = wp_get_attachment_url( $image_id );
        // var_dump($image_src);
        ?>
        
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
        $args = array(
            'post_parent' => $post->ID,
            'post_type'   => 'attachment', 
            'numberposts' => -1,
            'post_status' => 'inherit' 
		);
		?>
		<div class="kaisar-slideshow">
		<?php for($j=1; $j<=2; $j++){?>
			<div class="kslide-img slide<?php echo $j; ?>">
		<?php
		$i=1;
        $childrens = get_children( $args );
        foreach ( $childrens as $children ) {
        ?>
			<img id="slider_image_<?php echo $i;?>" src="<?php echo $children->guid; ?>" style="max-width:300px;" />
			
		<?php
			$i++;
        }
		?>
		</div>
	<?php } ?>
		</div>
		<input type="hidden" name="upload_image_id" id="upload_image_id" value="<?php echo $children->guid; ?>" />
		<p>
			<a title="<?php esc_attr_e( 'Set slider image' ) ?>" href="#" id="set-slider-image"><?php _e( 'Set slider image' ) ?></a>
			<!-- <a title="<?php esc_attr_e( 'Remove slider image' ) ?>" href="#" id="remove-slider-image" style="<?php echo ( ! $image_id ? 'display:none;' : '' ); ?>"><?php _e( 'Remove slider image' ) ?></a> -->
		</p>
		
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			
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
	
		});
		</script>
		<?php
	}
}

$GLOBALS['Kaisar_Slider'] = new Kaisar_Slider();

?>
<?php


if( !class_exists( 'WP_Image_Widget' ) ):

/**
 * WP Image Widget
 *
 * This wraps in the functionality to use images within your image by using the WordPress media
 * library functionality. 
 *
 * The credit for the logic for this class goes to Shane and Peter, Inc. <http://www.shaneandpeter.com> 
 * in their Image Widget plugin <http://wordpress.org/extend/plugins/image-widget/>.
 *
 * = How to Use =
 *
 * Include this file into your widget plugin and then have your widget class extend WP_Image_Widget
 * instead of WP_Widget.
 *
 * class MyWidget extends WP_Image_Widget { ... }
 * 
 * Use the class as you would for any other widget with the exception of two addition methods
 * for your use.
 * 
 * == wpiw_get_image_field( name, instance) ==
 * Use this when creating the widget form (in the from() method). Just pass in the name of the field
 * and the instance array that was passed into the form() method. It will return the HTML to add to
 * your form.
 *
 *   echo $this->wpiw_get_image_field( 'my_image', $instance );
 *
 * == wpiw_get_image_output( name, instance [,options] ==
 * Use this when output the widget (in the widget() method). Just pass in the name of the field,
 * the instance array that was passed into the widget() method, and the optional options array. 
 * You can change the size of the output image with the size option just as you would for a post
 * thumbnail. 
 *
 *   echo $this->wpiw_get_image_output( 'my_image', $instance,  array( 'size' => 'my_custom_defined_size' ) );
 */
class WP_Image_Widget extends WP_Widget {
	
	
	
	
	
	/**
	 * Constructor
	 */
	public function __construct( ){
		// pass all the arguments to the parent constructor
		$args = func_get_args();
		call_user_func_array( array(parent, '__construct'), $args );
		
		$this->wpiw_url = plugins_url( '', __FILE__ ). '/';
		add_action( 'admin_init', array( $this, 'wpiw_admin_init' ) );
	}
	
	
	
	/**
	 * Callback for admin_init hook
	 *
	 * Loads up scripts and stylesheets for the widget page and the media library
	 * 
	 * Attaches the following hooks image_send_to_editor and gettext.
	 *
	 * Registers new thumbnail size for the widget image preview.
	 *
	 */
	public function wpiw_admin_init(){
		// Load up the script for the Widget page
		global $pagenow;
		if (WP_ADMIN) {
			$this->wpiw_fix_async_upload_image();
			
			//add_action( 'admin_init', array( $this, 'fix_async_upload_image' ) );
			if ( 'widgets.php' == $pagenow ) {
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_style( 'wp-image-widget', $this->wpiw_url . 'wp-image-widget.css' );
				wp_enqueue_script( 'wp-image-widget', $this->wpiw_url . 'wp-image-widget.js' ,array('thickbox'), false, true );
				//add_action( 'admin_head-widgets.php', array( $this, 'admin_head' ) );
			} elseif ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
				add_filter( 'image_send_to_editor', array( $this ,'wpiw_image_send_to_editor'), 1, 8 );
				add_filter( 'gettext', array( $this, 'wpiw_replace_insert_button_text' ), 1, 3 );
				//add_filter( 'media_upload_tabs', array( $this, 'media_upload_tabs' ) );
				
				if( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'library' ){
					wp_enqueue_script( 'wp-image-widget-media', $this->wpiw_url . 'media-alter.js', array('jquery'), false, true );
				}
			}
			
			
		}
		
		add_image_size( 'wp-image-widget-preview', 150, 100 );
	}
	
	
	/**
	 * Fixes an issue with the media uploader
	 *
	 * Without this fix, an uploaded image cannot be inserted into the widget right away. You would
	 * have to upload the image, then close the thickbox, reopen the media thickbox and then insert 
	 * the image.
	 * 
	 * Credit for logic to Shane & Peter, Inc. (Peter Chester)
	 */
	function wpiw_fix_async_upload_image() {
		if(isset($_REQUEST['attachment_id'])) {
			$GLOBALS['post'] = get_post($_REQUEST['attachment_id']);
		}
	}
	
	
	
	/**
	 * Callback for hook image_send_to_editor
	 * 
	 * This instead of filtering the output for return, this instead attaches values via Javascript
	 * to be retrieved on the other side.
	 *
	 * Credit for logic to Shane & Peter, Inc. (Peter Chester)
	 */
	public function wpiw_image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt = '' ) {
		// Normally, media uploader return an HTML string (in this case, typically a complete image tag surrounded by a caption).
		// Don't change that; instead, send custom javascript variables back to opener.
		// Check that this is for the widget. Shouldn't hurt anything if it runs, but let's do it needlessly.
		if ( $this->wpiw_is_widget_context() ) {
			if ($alt=='') $alt = $title;
			?>
			<script type="text/javascript">
				// send image variables back to opener
				var win = window.dialogArguments || opener || parent || top;
				win.WPImageWidget = win.WPImageWidget || {};
				if( win.WPImageWidget.returnMedia ){
					win.WPImageWidget.returnMedia( 
						'<?php echo addslashes($html) ?>', 
						'<?php echo $id ?>',
						'<?php echo addslashes($alt) ?>',
						'<?php echo addslashes($caption) ?>',
						'<?php echo addslashes($title) ?>',
						'<?php echo $align ?>',
						'<?php echo $url ?>',
						'<?php echo $size ?>',
						'<?php $img = wp_get_attachment_image_src( $id, "wp-image-widget-preview" ); 
						echo addslashes( $this->wpiw_get_image( $img[0], array( "width" => $img[1], "height" => $img[2] ) ) ); 
						?>'
					);
				}
			</script>
			<?php
		}
		return $html;
	}
	
	
	
	
	/**
	 * Test context to see if the uploader is being used for the image widget or for other regular uploads
	 *
	 * @return void
	 * 
	 * Credit for logic to Shane & Peter, Inc. (Peter Chester)
	 */
	function wpiw_is_widget_context() {
		if ( isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],$this->id_base) !== false ) {
			return true;
		} elseif ( isset($_REQUEST['_wp_http_referer']) && strpos($_REQUEST['_wp_http_referer'],$this->id_base) !== false ) {
			return true;
		} elseif ( isset($_REQUEST['widget_id']) && strpos($_REQUEST['widget_id'],$this->id_base) !== false ) {
			return true;
		}
		return false;
	}
	
	
	
	
	/**
	 * Somewhat hacky way of replacing "Insert into Post" with "Insert into Widget"
	 *
	 * @param string $translated_text text that has already been translated (normally passed straight through)
	 * @param string $source_text text as it is in the code
	 * @param string $domain domain of the text
	 * @return void
	 */
	function wpiw_replace_insert_button_text( $translated_text, $source_text, $domain ) {
		if ( $this->wpiw_is_widget_context() ) {
			if ('Insert into Post' == $source_text) {
				return __('Insert Into Widget', $this->pluginDomain );
			}
		}
		return $translated_text;
	}
	
	
	
	
	/**
	 * Get the field code for an image
	 * 
	 * This creates the HTML to add to the widget form for an image.
	 *
	 * @param $field_name - the name of the field
	 *
	 * @return string - the HTML to include
	 */
	function wpiw_get_image_field( $field_name, $instance, $field_title='Image' ){
		$field_value = isset( $instance[ $field_name ] ) ? $instance[ $field_name ] : '';
		$has_image = isset($instance[ $field_name ]) && !empty( $instance[ $field_name ] );
		
		$media_upload_iframe_src = "media-upload.php?type=image&amp;widget_id=".$this->id; //NOTE #1: the widget id is added here to allow uploader to only return array if this is used with image widget so that all other uploads are not harmed.
		$image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src");
		
		$media_library_link_attrs = '
			data-widget-id="' . $this->id . '" 
			data-widget-field="' . $this->get_field_name( $field_name ) . '" 
			data-widget-field-id="' . $this->get_field_id( $field_name )  . '"';
		
		$image_icon = '<img src="' . admin_url( 'images/media-button-image.gif' ) . '" alt="" align="absmiddle" /> ';
		
		$o = '<div id="'. $this->get_field_id( $field_name ) . '--field" class="wp-image-widget-field';
		$o .= $has_image? ' wp-image-widget-image--with-image' : ' wp-image-widget-image--without-image';
		$o .= '">';
		
		$o .= '<input id="' . $this->get_field_id( $field_name ) . '" name="' . $this->get_field_name( $field_name ) . '" type="hidden" value="' . $field_value . '" />';
		
		$o .= '<div class="wp-image-widget-image">';
		if( $has_image ){
			
			$o .= $this->wpiw_get_image_output( $field_name, $instance, array( 'size' => 'wp-image-widget-preview', 'id' => $this->get_field_id( $field_name ).'--preview' ) );
		} else {
			$o .= '<span id="'. $this->get_field_id( $field_name ) .'--preview"></span>';
		}
		
		$o .= ' &nbsp;</div>';
		
		$o .= '<div class="wp-image-widget-image--buttons wp-image-widget-image--add-image-buttons">';
		$o .= '<a href="' . $image_upload_iframe_src . '&amp;TB_iframe=true"
			' . $media_library_link_attrs . '
			id="add_image-' . $this->get_field_id( $field_name ) . '"
			class="button wp-image-widget-image--open-media-library" 
			title="' . __( 'Add '.$field_title ) . '" 
			>' . $image_icon . __( 'Add '.$field_title ) . '</a>';
		$o .= '</div>';
		
		$o .= '<div class="wp-image-widget-image--buttons wp-image-widget-image--change-image-buttons">';
		$o .= '<a href="' . $image_upload_iframe_src . '&amp;tab=library&amp;imageId='.$field_value.'&amp;TB_iframe=true" class="button wp-image-widget-image--open-media-library" ' . $media_library_link_attrs . '>' . $image_icon . __( 'Change '.$field_title ) . '</a>';
		$o .= '<a class="button delete wp-image-widget-image--remove-image">' . __( 'Remove '.$field_title ) . '</a>';
		$o .= '</div>';
		
		$o .= '</div>';
		return $o;
	}
	
	
	
	
	/**
	 * Get the HTML for the image to output
	 * 
	 * @param $field_name string - the name of the field to get
	 * @param $instance array - the $instance arguments passed into the widget method
	 * @param $options array (optional) - various options for outputting the image.
	 * 
	 * @return string - the output
	 */
	function wpiw_get_image_output( $field_name, $instance, $options=array() ){
		
		if( !isset( $instance[ $field_name ] ) || empty( $instance[ $field_name ] ) ){
			return '';
		}
		
		$options = array_merge( array(
				'raw' => false,
				'size' => 'post-thumbnail',
				'classes' => array(),
			), $options );
		extract( $options );
		
		$size = apply_filters( 'post_thumbnail_size', $size );
		do_action( 'begin_fetch_post_thumbnail_html', $post_id, $post_thumbnail_id, $size );
		$img = wp_get_attachment_image_src( $instance[ $field_name ], $size );
		do_action( 'end_fetch_post_thumbnail_html', $post_id, $post_thumbnail_id, $size );
		
		
		if( $img === false ){
			return '';
		}
		
		if( $raw ){
			return $img;
		}
		
		$attrs = array(
			'width' => $img[1],
			'height' => $img[2]
		);
		
		if( isset( $alt ) ){
			$attrs['alt'] = $alt;
		}
		
		if( isset( $title ) ){
			$attrs['title'] = $title;
		}
		
		if( isset( $classes ) ){
			$attrs['class'] = implode( ' ', $classes );
		}
		
		if( isset( $id ) ){
			$attrs['id'] = $id;
		}
		
		
		
		return $this->wpiw_get_image( $img[0], $attrs );
	}
	
	
	
	/**
	 * Create an the HTML for an image
	 *
	 * A helper for generating the HTML for an image.
	 *
	 * @param $src string - the src for the image
	 * @param $attrs array (optional) - an array of attributes to add to the image
	 * @return HTML
	 */
	function wpiw_get_image( $src, $attrs=array() ){
		$o .= '<img src="' . $src . '" ';
		$o .= 'alt="' . ( isset($attrs['alt'])? $attrs['alt'] : '' ) . '" ';
		unset( $attrs['alt'] );
		
		foreach( $attrs as $k => $v ){
			if( is_array( $v ) ){
				$v = implode( $v );
			}
			
			$o .= $k . '="'. $v . '" ';
		}
		
		$o .= ' />';
		return $o;
	}
}





endif; // !class_exists( 'WP_Image_Widget' )


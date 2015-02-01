<?php
/**
 * NewpostCatch class
 **/
if ( !class_exists('NewpostCatch') ) {
	class NewpostCatch extends WP_Widget {
		/*** variables ***/
		var $version = "1.2.7";
		var $pluginDir = "";

		/*** structure ***/
		function NewpostCatch() {
			/** widget settings **/
			$widget_ops = array( 'description' => 'Thumbnails in new articles.' );

			/** widget actual processes **/
			parent::WP_Widget(false, $name = 'Newpost Catch', $widget_ops );

			/** plugin path **/
			if (empty($this->pluginDir)) $this->pluginDir = WP_PLUGIN_URL . '/newpost-catch';

			/** default thumbnail **/
			$this->default_thumbnail = $this->pluginDir . "/no_thumb.png";

			/** charset **/
			$this->charset = get_bloginfo('charset');

			/** print stylesheet **/
			add_action( 'get_header', array( &$this, 'enqueue_stylesheet' ) );

			/** activate textdomain for translations **/
			add_action( 'init', array( &$this, 'NewpostCatch_textdomain') );
		}

		/** plugin localization **/
		function NewpostCatch_textdomain() {
			load_plugin_textdomain ( 'newpost-catch', false, basename( rtrim(dirname(__FILE__), '/') ) . '/languages' );
		}

		/** plugin enqueue_stylesheet **/
		function enqueue_stylesheet() {
			if( get_option( 'widget_newpostcatch' ) ){
				$options = array_filter( get_option( 'widget_newpostcatch' ) );
				unset( $options['_multiwidget'] );
				foreach( $options as $key => $val ) {
					$options[$key] = $val['css']['active'];
				}
				if( in_array('on' , $options) ){
					$css_path = plugins_url('newpost-catch/style.css');
				} else {
					$css_path = ( @file_exists(STYLESHEETPATH.'/css/newpost-catch.css') ) ? get_stylesheet_directory_uri() . '/css/newpost-catch.css' : "" ;
				}

				// register CSS
				wp_register_style( 'newpost-catch', $css_path, array(), $this->version );
				wp_enqueue_style( 'newpost-catch' );
			}
		}

		/**▼ create widget ▼**/
		function widget($args, $instance) {
			extract( $args );

			$title		= apply_filters('NewpostCatch_widget_title', $instance['title']);
			$width		= apply_filters('NewpostCatch_widget_width', $instance['width']);
			$height 	= apply_filters('NewpostCatch_widget_height', $instance['height']);
			$number 	= apply_filters('NewpostCatch_widget_number', $instance['number']);
			$ignore 	= apply_filters('NewpostCatch_widget_ignore', $instance['ignore_check']['active']);
			$css		= apply_filters('NewpostCatch_widget_css', $instance['css']);
			$cat		= apply_filters('NewpostCatch_widget_cat', $instance['cat']);
			if( !empty($instance['post_type']) ){
				$post_type	= apply_filters('NewpostCatch_widget_post_type', $instance['post_type']);
			} else {
				$post_type	= apply_filters('NewpostCatch_widget_post_type', 'post');
			}
/*			if( $instance['ignore_check']['active'] = !false  ) { $ignore = 1; } else { $ignore = 0; }*/

			if( !function_exists('no_thumb_image') ){
				function no_thumb_image() {
					ob_start();
					ob_end_clean();
					preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', get_the_content(), $matches );

					if( isset($matches[1][0]) && !is_wp_error($matches[1][0]) ){
						$set_img = $matches[1][0];
					} else {
						$set_img = WP_PLUGIN_URL . '/newpost-catch' . '/no_thumb.png';
					}
					return $set_img;
				}
			}

			echo $before_widget;

			if ( $title ) echo $before_title . $title . $after_title;
				$sticky = get_option( 'sticky_posts' );
				if( $ignore == !false ){
					$npc_query = new WP_Query( array(
						'post_type' => $post_type,
						'cat' => $cat,
						'posts_per_page' => $number,
						'ignore_sticky_posts' => 0,
						'orderby' => 'date',
						'order' => 'DESC'
					));
				} else {
					$npc_query = new WP_Query( array(
						'post_type' => $post_type,
						'cat' => $cat,
						'posts_per_page' => $number,
						'post_not_in' => $sticky,
						'ignore_sticky_posts' => 1,
						'orderby' => 'date',
						'order' => 'DESC'
					));
				}
?>
<ul id="npcatch" >
<?php if( $npc_query->have_posts() ) : ?>
<?php $i = 0; ?>
<?php while( $npc_query->have_posts() ) : $npc_query->the_post(); ?>
<li>
<a href="<?php echo esc_html( get_permalink() ); ?>" title="<?php esc_attr( the_title() ); ?>" >
<?php if( has_post_thumbnail() ) { ?>
<?php
$thumb_id = get_post_thumbnail_id();
$thumb_url = wp_get_attachment_image_src($thumb_id);
$thumb_url = $thumb_url[0];
?>
<img src="<?php echo esc_attr( $thumb_url ); ?>" width="<?php echo esc_attr( $width ); ?>" height="<?php echo esc_attr( $height ); ?>" alt="<?php esc_attr( the_title() ); ?>" title="<?php esc_attr( the_title() ); ?>"  />
<?php } else { ?>
<img src="<?php echo esc_attr( no_thumb_image() ); ?>"  width="<?php echo esc_attr( $width ); ?>" height="<?php echo esc_attr( $height ); ?>" alt="<?php esc_attr( the_title() ); ?>" title="<?php esc_attr( the_title() ); ?>" />
<?php } ?>
</a>
<span class="title"><a href="<?php echo esc_html( get_permalink() ); ?>" title="<?php esc_attr( the_title() ); ?>"><?php esc_html( the_title() ); ?>
<?php if ( $instance['date']['active'] == true ) { ?>
<span class="date"><?php echo esc_html( get_the_time( get_option('date_format') ) ); ?></span>
<?php } ?>
</a></span>
</li>
<?php $i++; ?>
<?php endwhile; ?>
<?php else : ?>
<p>no post</p>
<?php endif; wp_reset_postdata(); ?>
</ul>

<?php
			echo $after_widget;
		}
		/**▲ create widget ▲**/

		/** @see WP_Widget::update **/
		// updates each widget instance when user clicks the "save" button
		function update($new_instance, $old_instance) {

			$instance = $old_instance;

			$instance['title']			= ($this->magicquotes) ? htmlspecialchars( stripslashes(strip_tags( $new_instance['title'] )), ENT_QUOTES ) : htmlspecialchars( strip_tags( $new_instance['title'] ), ENT_QUOTES );
			$instance['width']			= is_numeric($new_instance['width']) ? $new_instance['width'] : 10;
			$instance['height']			= is_numeric($new_instance['height']) ? $new_instance['height'] : 10;
			$instance['number']			= is_numeric($new_instance['number']) ? $new_instance['number'] : 5;

			if( preg_match("/^[0-9]|,|-/", $new_instance['cat']) ){
				$instance['cat'] 		= $new_instance['cat'];
			} else {
				$instance['cat'] 		= "";
			}

			$instance['date']['active']		= $new_instance['date'];
			$instance['ignore_check']['active']	= $new_instance['ignore_check']['active'];
			$instance['css']['active']		= $new_instance['css'];
			$instance['post_type']			= !empty($new_instance['post_type']) ? $new_instance['post_type'] : 'post';

			update_option('newpost_catch', $instance);

			return $instance;
		}

		/** @see WP_Widget::form **/
		function form($instance) {

			/** define default value **/
			$defaults = array(
						'title'		=> __('LatestPost(s)' , 'newpost-catch'),
						'width'		=> 10,
						'height'	=> 10,
						'number'	=> 5,
						'date'		=> array( 'active' => false ),
						'ignore_check'	=> array( 'active' => false ),
						'css'		=> array( 'active' => true ),
						'cat'		=> NULL,
						'post_type'	=> 'post',
				    );

			$instance = wp_parse_args( (array) $instance, $defaults );
?>
			<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title' , 'newpost-catch'); ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" class="widefat" value="<?php echo esc_attr($instance['title']); ?>" />
			</p>
			<p>
			<?php _e('Thumbnail Size' , 'newpost-catch'); ?><br />
			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width' , 'newpost-catch'); ?></label>
			<input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" style="width:50px" value="<?php echo esc_attr($instance['width']); ?>" /> px
			<br />
			<label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height' , 'newpost-catch'); ?></label>
			<input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" style="width:50px;" value="<?php echo esc_attr($instance['height']); ?>" /> px
			</p>
			<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Show post(s)' , 'newpost-catch'); ?></label>
			<input style="width:30px;" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr($instance['number']); ?>" /> <?php _e('Post(s)', 'newpost-catch'); ?>
			</p>
			<p>
	                <input type="checkbox" class="checkbox" <?php echo ($instance['date']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" /> <label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e('Display date', 'newpost-catch'); ?></label>
			</p>
			<p>
	                <input type="checkbox" class="checkbox" <?php echo ($instance['ignore_check']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'ignore_check' ); ?>" name="<?php echo $this->get_field_name( 'ignore_check' ); ?>" /> <label for="<?php echo $this->get_field_id( 'ignore_check' ); ?>"><?php _e('Display sticky post', 'newpost-catch'); ?></label>
			</p>
			<p>
	                <input type="checkbox" class="checkbox" <?php if($instance['css']['active']){ echo 'checked="checked"'; } else { echo ''; } ?> id="<?php echo $this->get_field_id( 'css' ); ?>" name="<?php echo $this->get_field_name( 'css' ); ?>" /> <label for="<?php echo $this->get_field_id( 'css' ); ?>"><?php _e('Use default css', 'newpost-catch'); ?></label>
			</p>
			<?php _e('Post types' , 'newpost-catch'); ?><br />
<?php
	$args = array(
		'public'   => true,
	);

	$output = 'objects';
	$operator = 'and';

	$post_types = get_post_types( $args, $output, $operator );
	foreach ( $post_types as $post_type ) {
		if( $post_type->name !== 'attachment' ){
?>
   <p><input type="radio" id="<?php echo $this->get_field_name($post_type->name); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" value="<?php echo $post_type->name; ?>" <?php echo ( $instance['post_type'] == $post_type->name ) ? 'checked="checked"' : ''; ?> > <label for="<?php echo $this->get_field_name($post_type->name); ?>"><?php echo $post_type->labels->singular_name . '(' . $post_type->name . ')'; ?></label></p>
<?php
		}
	}
?>
<?php if( $instance['post_type'] == 'post' ){ ?>
			<p>
			<label for="<?php echo $this->get_field_id('cat'); ?>"><?php _e('Display category(ies)' , 'newpost-catch'); ?></label>
			<input id="<?php echo $this->get_field_id('cat'); ?>" name="<?php echo $this->get_field_name('cat'); ?>" type="text" class="widefat" value="<?php echo esc_attr($instance['cat']); ?>" />
			<span><a href="<?php echo get_bloginfo('url') . '/wp-admin/edit-tags.php?taxonomy=category'; ?>"><?php _e('Check the category ID' , 'newpost-catch'); ?></a></span>
			</p>
<?php } ?>
			<p>
				<?php _e('Use shortcode' , 'newpost-catch'); ?>
				<?php _e('Can use the shortcode in a textwidget and theme files.' , 'newpost-catch'); ?> <a href="http://wordpress.org/plugins/newpost-catch/faq/" target="_blank">FAQ</a>
			</p>
			<p>
			<span><a href="<?php echo get_site_url() . '/wp-admin/options-general.php?page=Newpost-Catch.php'; ?>"><?php _e('Setting Thumbnails' , 'newpost-catch'); ?></a></span>
			</p>
			<p>
				<?php _e('Contact/Follow' , 'newpost-catch'); ?>
				<a href="https://twitter.com/NewpostCatch" target="_blank">Twitter</a>
				<a href="https://www.facebook.com/NewpostCatch" target="_blank">Facebook</a>
			</p>
<?php
		}
	}
}

if ( !class_exists('NewpostCatch_SC') ) {
	class NewpostCatch_SC {
		function __construct(){
			add_shortcode('npc', array(&$this, 'npc_sc'));

			function no_thumb_image() {
				ob_start();
				ob_end_clean();
				preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', get_the_content(), $matches );
				if( isset( $matches[1][0]) ){
					$set_img = $matches[1][0];
				} else {
					$set_img = WP_PLUGIN_URL . '/newpost-catch' . '/no_thumb.png';
				}
				return $set_img;
			}
		}
		
		function npc_sc($atts) {

			/** default value **/
			extract( shortcode_atts( array(
				'id' => "npcatch",
				'post_type' => "post",
				'cat' => NULL,
				'width' => 10,
				'height' => 10,
				'posts_per_page' => 5,
				'sticky' => 0,
				'offset' => 0,
				'orderby' => "date",
				'order' => "DESC",
				'date' => 0,
				'dynamic' => 0,
			), $atts ) );

			if( is_array($atts) && array_key_exists('dynamic',$atts) && $atts['dynamic'] == 1 && get_post_type() == "post" && is_single() ){
				$cat = get_the_category();
				$cat = $cat[0];
				$cat = $cat->cat_ID;
			} else {
				if( is_null($cat) ){
					$cat = NULL;
				}
			}

			if( is_array($atts) && array_key_exists('sticky',$atts) && $atts['sticky'] == 1 ){
				$sticky = 0;
			} else {
				$sticky = 1;
			}

			/** query **/
			$npc_sc_query = new WP_Query( array(
				'post_type' => $post_type,
				'cat' => $cat,
				'offset' => $offset,
				'posts_per_page' => $posts_per_page,
				'ignore_sticky_posts' => $sticky,
				'orderby' => $orderby,
				'order' => $order
			));

			$html = "";
			if( $npc_sc_query->have_posts() ) :
				$html .= "<ul id=\"$id\">\n";
				while( $npc_sc_query->have_posts() ) :
					$npc_sc_query->the_post();
					
					$html .= "<li>";
					$html .= "<a href=\"" . esc_html( get_permalink() ) . "\" title=\"" . esc_attr( get_the_title() ) . "\" >\n";
					$thumb_url = "";
				if( has_post_thumbnail() ) {
					$thumb_id = get_post_thumbnail_id();
					$thumb_url = wp_get_attachment_image_src($thumb_id);
					$thumb_url = $thumb_url[0];
					$html .= "<img src=\"" . esc_attr( $thumb_url ) . "\" width=\"" . esc_attr( $width ) . "\" height=\"" . esc_attr( $height ) . "\" alt=\"" . esc_attr( get_the_title() ) . "\" title=\"" . esc_attr( get_the_title() ) . "\" /></a>\n";
				} else {
					$html .= "<img src=\"" . esc_attr( no_thumb_image() ) . "\" width=\"" . esc_attr( $width ) . "\" height=\"" . esc_attr( $height ) . "\" alt=\"" . esc_attr( get_the_title() ) . "\" title=\"" . esc_attr( get_the_title() ) . "\" /></a>\n";
				}
					$html .= "<span class=\"title\"><a href=\"" . esc_html( get_permalink() ) . "\" title=\"" . esc_attr( get_the_title() ) . "\" >" . esc_html( get_the_title() );
				if ( $date == true ) {
					$html .= "<span class=\"date\">" . esc_html( get_the_time( get_option('date_format') ) ) . "</span>\n";
				}
					$html .= "</a></span></li>\n";
				endwhile;
				$html .= "</ul>";
			endif;
			wp_reset_postdata();
			
			return $html;
		}
	}
}
?>
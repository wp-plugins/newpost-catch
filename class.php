<?php
/**
 * NewpostCatch class
 **/
if ( !class_exists('NewpostCatch') ) {
	class NewpostCatch extends WP_Widget {
		/*** plugin variables ***/
		var $version = "1.1.2";
		var $pluginDir = "";

		/*** plugin structure ***/
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
			add_action( 'wp_head', array(&$this, 'NewpostCatch_print_stylesheet') );

			/** activate textdomain for translations **/
			add_action( 'init', array(&$this, 'NewpostCatch_textdomain') );
		}

		/** plugin localization **/
		function NewpostCatch_textdomain() {
			load_plugin_textdomain ( 'newpost-catch', false, basename( rtrim(dirname(__FILE__), '/') ) . '/languages' );
		}

		/** plugin insert header stylesheet **/
		function NewpostCatch_print_stylesheet() {
			if( get_option( 'widget_newpostcatch' ) ){
				$options = array_filter( get_option( 'widget_newpostcatch' ) );
				unset( $options['_multiwidget'] );
				foreach( $options as $key => $val ) {
					$options = $options[$key];
				}
				if( $options['css']['active'] ){
					$css_path = plugin_dir_url( __FILE__ ) . 'style.css';
				} else {
					$css_path = ( @file_exists(TEMPLATEPATH.'/css/newpost-catch.css') ) ? get_stylesheet_directory_uri().'/css/newpost-catch.css' : "" ;
				}
				if( $css_path ){
					echo "\n"."<!-- Newpost Catch ver".$this->version." -->"."\n".'<link rel="stylesheet" href="' . $css_path . '" type="text/css" media="screen" />'."\n"."<!-- End Newpost Catch ver".$this->version." -->"."\n";
				}
			}

//			$css_path = ( @file_exists(TEMPLATEPATH.'/css/newpost-catch.css') ) ? get_stylesheet_directory_uri().'/css/newpost-catch.css' : plugin_dir_url( __FILE__ ).'style.css';
//			echo "\n"."<!-- Newpost Catch ver".$this->version." -->"."\n".'<link rel="stylesheet" href="' . $css_path . '" type="text/css" media="screen" />'."\n"."<!-- End Newpost Catch ver".$this->version." -->"."\n";
		}

		/**▼ create widget ▼**/
		function widget($args, $instance) {
			extract( $args );

			$title	= apply_filters('NewpostCatch_widget_title', $instance['title']);
			$width	= apply_filters('NewpostCatch_widget_width', $instance['width']);
			$height = apply_filters('NewpostCatch_widget_height', $instance['height']);
			$number = apply_filters('NewpostCatch_widget_number', $instance['number']);
			$ignore = apply_filters('NewpostCatch_widget_ignore', $instance['ignore_check']['active']);
			$css	= apply_filters('NewpostCatch_widget_css', $instance['css']);
			$cat	= apply_filters('NewpostCatch_widget_cat', $instance['cat']);
/*			if( $instance['ignore_check']['active'] = !false  ) { $ignore = 1; } else { $ignore = 0; }*/

			if( !function_exists('no_thumb_image') ){
				function no_thumb_image() {
					ob_start();
					ob_end_clean();
					$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', get_the_content(), $matches );
					$set_img = $output[1][0];

					/* if not exist images */
					if( empty( $set_img ) ){
						$set_img = WP_PLUGIN_URL . '/newpost-catch' . '/no_thumb.png';
					}
					return $set_img;
				}
			}

			echo $before_widget;

			if ( $title ) echo $before_title . $title . $after_title;
/*
				query_posts("showposts=" . $number .
					    "&ignore_sticky_posts=" . $ignore .
					    "&cat=" . $cat
					     );
*/
				$sticky = get_option( 'sticky_posts' );
				if( $ignore == !false ){
/*
					if( ($number - count($sticky)) > 0 ){
						$number = ($number - count($sticky));
					}
*/
					$npc_query = new WP_Query( array(
						'cat' => $cat,
						'posts_per_page' => $number,
						'ignore_sticky_posts' => 0,
						'orderby' => 'date',
						'order' => 'DESC'
					));
				} else {
					$npc_query = new WP_Query( array(
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
<?php while( $npc_query->have_posts() ) : $npc_query->the_post(); ?>
<li>
<a href="<?php echo esc_html( get_permalink() ); ?>" title="<?php esc_attr( the_title() ); ?>" >
<?php if( has_post_thumbnail() ) { ?>
<?php /*\n . the_post_thumbnail( array( $width , $height ),array( 'alt' => $title_attr , 'title' => $title_attr ));*/ ?>
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
			$instance['cat'] 			= $new_instance['cat'];
} else {
			$instance['cat'] 			= "";
}

			$instance['date']['active']		= $new_instance['date'];
			$instance['ignore_check']['active']	= $new_instance['ignore_check']['active'];
			$instance['css']['active']		= $new_instance['css'];

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
						'cat'		=> NULL
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
			<input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" style="width:30px" value="<?php echo esc_attr($instance['width']); ?>" /> px
			<br />
			<label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height' , 'newpost-catch'); ?></label>
			<input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" style="width:30px;" value="<?php echo esc_attr($instance['height']); ?>" /> px
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
			<p>
			<label for="<?php echo $this->get_field_id('cat'); ?>"><?php _e('Display category(ies)' , 'newpost-catch'); ?></label>
			<input id="<?php echo $this->get_field_id('cat'); ?>" name="<?php echo $this->get_field_name('cat'); ?>" type="text" class="widefat" value="<?php echo esc_attr($instance['cat']); ?>" />
			<span><a href="<?php echo get_bloginfo('url') . '/wp-admin/edit-tags.php?taxonomy=category'; ?>"><?php _e('Check the category ID' , 'newpost-catch'); ?></a></span>
			</p>
			<p>
			<label><?php _e('Contact/Follow' , 'newpost-catch'); ?></label>
				<a href="https://twitter.com/s56bouya" target="_blank">Twitter</a>
				<a href="http://www.facebook.com/imamura.tetsuya" target="_blank">Facebook</a>
				<a href="https://plus.google.com/b/103773364658434530979/" target="_blank">Google+</a>
			</p>
<?php
		}
	}
}
?>
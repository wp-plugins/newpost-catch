<?php
/**
 * NewpostCatch class
 **/
if ( !class_exists('NewpostCatch') ) {
	class NewpostCatch extends WP_Widget {
		/*** plugin variables ***/
		var $version = "1.0.2";
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
		
		/** insert header stylesheet **/
		function NewpostCatch_print_stylesheet() {
			$css_path = ( @file_exists(TEMPLATEPATH.'/css/newpost-catch.css') ) ? get_stylesheet_directory_uri().'/css/newpost-catch.css' : plugin_dir_url( __FILE__ ).'style.css';
			echo "\n"."<!-- Newpost Catch ver".$this->version." -->"."\n".'<link rel="stylesheet" href="' . $css_path . '" type="text/css" media="screen" />'."\n"."<!-- End Newpost Catch ver".$this->version." -->"."\n";	
		}
		
		/**¥ create widget ¥**/
		function widget($args, $instance) {
			extract( $args );
			
			$title = apply_filters('NewpostCatch_widget_title', $instance['title']);
			$width = apply_filters('NewpostCatch_widget_width', $instance['width']);
			$height = apply_filters('NewpostCatch_widget_height', $instance['height']);
			$number = apply_filters('NewpostCatch_widget_number', $instance['number']);
			
			echo $before_widget;
			
			if ( $title ) echo $before_title . $title . $after_title;
				query_posts($query_string . "&showposts=" . $number . "&ignore_sticky_posts=1" );

?>
<ul id="npcatch" >
<?php if( have_posts() ) : ?>
<?php while( have_posts() ) : the_post(); ?>
<li>
<span class="thumb"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
<?php if( has_post_thumbnail() ) : ?>
<?php \n . the_post_thumbnail( array( $width , $height ),array( 'alt' => $title_attr , 'title' => $title_attr )); ?>
<?php else : ?>
<img src="<?php echo $this->default_thumbnail ?>" width="<?php echo $width ?>" height="<?php echo $height ?>" >
<?php endif; ?>
</a></span>
<span class="title"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?>
<?php if ( $instance['date']['active'] ) { ?>
<span class="date"><?php the_time('[Y/m/d]' , '' , '' ); ?></span>
<?php } ?>
</a></span>
</li>
<?php endwhile; ?>
<?php else : ?>
<p>no post</p>
<?php endif; ?>
</ul>
<?php
			echo $after_widget;
		}
		/**£ create widget £**/

		/** @see WP_Widget::update **/
		// updates each widget instance when user clicks the "save" button
		function update($new_instance, $old_instance) {
			
			$instance = $old_instance;
			
			$instance['title'] = ($this->magicquotes) ? htmlspecialchars( stripslashes(strip_tags( $new_instance['title'] )), ENT_QUOTES ) : htmlspecialchars( strip_tags( $new_instance['title'] ), ENT_QUOTES );
			$instance['width'] = is_numeric($new_instance['width']) ? $new_instance['width'] : 10;
			$instance['height'] = is_numeric($new_instance['height']) ? $new_instance['height'] : 10;
			$instance['number'] = is_numeric($new_instance['number']) ? $new_instance['number'] : 5;
			$instance['date']['active'] = $new_instance['date'];
			
			//return $instance;
			return $instance;
		}
		 
		/** @see WP_Widget::form **/
		function form($instance) {
			$title = esc_attr($instance['title']);
			$width = esc_attr($instance['width']);
			$height = esc_attr($instance['height']);
			$number = esc_attr($instance['number']);
			$defaults = array( 'date' => array( 'active' => false ) );
?>
			<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:' , 'newpost-catch'); ?>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" class="widefat" value="<?php echo $title; ?>" /></label>
			</p>
			<p>
			<?php _e('Thumbnail Size' , 'newpost-catch'); ?><br />
			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:' , 'newpost-catch'); ?>
			<input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="number" style="width:30px" value="<?php echo $width; ?>" /> px</label>
			<br />
			<label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height:' , 'newpost-catch'); ?>
			<input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="number" style="width:30px;" value="<?php echo $height; ?>" /> px</label>
			</p>
			<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Showposts:' , 'newpost-catch'); ?>
			<input style="width:30px;" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" value="<?php echo $number; ?>" /></label> <?php _e('Posts', 'newpost-catch'); ?>
			</p>
			<p>
	                <input type="checkbox" class="checkbox" <?php echo ($instance['date']['active']) ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" /> <label for="<?php echo $this->get_field_id( 'date' ); ?>"><?php _e('Display date', 'newpost-catch'); ?></label>
			</p>
<?php
		}
	}
}
?>
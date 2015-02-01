<?php
//Init
if( isset($_POST['number']) && preg_match("/^[0-9]+$/", $_POST['number']) ){
	$number = $_POST['number'];
} else {
	$number = 5;
}
if( isset($set_array) ){
	$set_array = array();
}

//Send to Submit Button
if ( !empty($_POST) && isset( $_POST['npc_submit'] ) ) {
	//Check Adminn Referer
	check_admin_referer("npc_options" , "npc_submit_wpnonce" ); 

	//Get Option
	$set_array = get_option('npc_search_posts');

	//Generate Thumbnails
	foreach( $set_array as $set ){
		//Up Dir
		$upload_dir = wp_upload_dir();
		$image_data = file_get_contents($set['img_url']);
		$filename = basename($set['img_url']);
		if(wp_mkdir_p($upload_dir['path']))
			$file = $upload_dir['path'] . '/' . $filename;
		else
			$file = $upload_dir['basedir'] . '/' . $filename;

		file_put_contents($file, $image_data);

		$wp_filetype = wp_check_filetype($filename, null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => sanitize_file_name($filename),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file, $set['ID'] );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		//Set Thumbnail
		set_post_thumbnail( $set['ID'], $attach_id );
		delete_option('npc_search_posts');
	}
	//Display Messages
	echo "<div class=\"updated\"><p><strong>" . __('completed.','newpost-catch') . "</strong></p></div>";
}
?>

<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field( "npc_options" , "npc_submit_wpnonce" ); ?>
	<div class="wrap">
		<h2>Newpost Catch <?php _e('Setting Thumbnails','newpost-catch'); ?></h2>
		<h3><?php _e('Eyecatch image batch setting','newpost-catch'); ?></h3>
		<h4><?php _e('I can set the eyecatch image, [the first image] found in the body of the post. (※ image does not exist, invalid image of (dead link) can not be set the URL.','newpost-catch'); ?></h4>
		<div class="postbox">
			<div class="inside">
				<strong><?php _e('Search the post.','newpost-catch'); ?></strong>
				<p><?php _e('LatestPost(s)','newpost-catch'); ?> <input type="text" name="number" value="<?php echo intval($number); ?>" size="4"> <?php _e('Post(s)','newpost-catch'); ?> <input type="submit" class="button" name="search_posts" value="<?php _e('Search','newpost-catch'); ?>" /></p>
			</div>
		</div>
<?php
	if ( !empty($_POST) && isset( $_POST['search_posts'] ) ) {
		//Check Adminn Referer
		check_admin_referer("npc_options" , "npc_submit_wpnonce" ); 

		global $wpdb;
		$data = $wpdb->get_results("SELECT ID,post_title,post_content FROM {$wpdb->posts} p where p.post_status = 'publish' AND p.post_content REGEXP '<img[^>]+src=\"([^\">]+)\"' AND p.post_type IN('post') AND  p.ID NOT IN ( SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_thumbnail_id')) ORDER BY p.ID DESC LIMIT $number");

//		var_dump($data);

		if( $data ){
			$set_array = array();
			$set_count = 1;
?>
<hr />
			<h3><?php _e('Search Result','newpost-catch'); ?></h3>
			<h4><?php _e('Can be edited post in the [Edit] button. Please confirm the image displayed in the text and, whether or not broken links.','newpost-catch'); ?></h4>
			<table class="wp-list-table widefat fixed">
				<thead>
					<tr>
						<th width="10%"><?php _e('Edit','newpost-catch'); ?></th>
						<th width="30%"><?php _e('Title','newpost-catch'); ?></th>
						<th width="30%"><?php _e('Image','newpost-catch'); ?></th>
						<th width="30%"><?php _e('Image URL','newpost-catch'); ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
				<?php foreach( $data as $result ){ ?>
					<tr <?php if( $set_count % 2 == 1 ){ echo 'class="alternate"'; } ?>>
<?php
				$set_img = "";
				preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $result->post_content, $matches );
				if( isset($matches[1][0]) && !is_wp_error($matches[1][0]) ){
					$file_check = @get_headers($matches[1][0]);
					if( preg_match('#^HTTP/.*\s+[200|302]+\s#i', $file_check[0]) ) {
						$set_img = $matches[1][0];
						$set_array[] = array('ID'=>$result->ID,'img_url'=>$matches[1][0]);
					}
				} else {
					$set_img = WP_PLUGIN_URL . '/newpost-catch' . '/no_thumb.png';
				}
				echo "<td><a class=\"button\" href=\"" . get_edit_post_link($result->ID) . "\">" . __('Edit','newpost-catch') . "</a></td>";
				echo "<td>" . $result->post_title . "</td>";
				echo "<td>";
				if( $set_img != "" ){
				echo "<img src=\"" . $set_img . "\" width=\"" . 80 . "\" >";
				}
				echo "</td>";
				echo "<td>" . $set_img . "</td>";
?>
					</tr>
<?php
				$set_count++;
				}
?>
				</tbody>
				<tfoot>
					<tr>
						<th width="10%"><?php _e('Edit','newpost-catch'); ?></th>
						<th width="30%"><?php _e('Title','newpost-catch'); ?></th>
						<th width="30%"><?php _e('Image','newpost-catch'); ?></th>
						<th width="30%"><?php _e('Image URL','newpost-catch'); ?></th>
					</tr>
				</tfoot>
			</tbody>
		</table>
<?php if( count($set_array) > 0 ){ ?>
		<h3><span style="color:#4AA21A; font-weight:bold; font-size:16px;"><?php echo count($set_array); ?></span> <?php _e('Image(s) found.','newpost-catch'); ?></h3>
		<h4><?php _e('Setting start in the [Generate] button','newpost-catch'); ?></h4>
		<p class="submit"><input type="submit" class="button-primary" name="npc_submit" value="<?php echo _e('Generate','newpost-catch'); ?>" /></p>
<?php } else { ?>
		<h3><?php _e('Not found.','newpost-catch'); ?></h3>
<?php } ?>
<hr />
		<h3><?php _e('Thumbnail','newpost-catch'); ?></h3>
		<p><?php _e('Width'); ?><?php echo get_option('thumbnail_size_w'); ?>px</p>
		<p><?php _e('Height'); ?><?php echo get_option('thumbnail_size_h'); ?>px</p>
		<p><?php if( get_option('thumbnail_crop') == 1 ) { _e('Crop thumbnail to exact dimensions (normally thumbnails are proportional)'); } ?></p>
		<p><?php _e('Configuration','newpost-catch'); ?> <a href="<?php echo get_bloginfo('url') . '/wp-admin/options-media.php'; ?>"><?php _e('Media Settings','newpost-catch'); ?></a></p>
<?php
		//var_dump($set_array);
		update_option('npc_search_posts', $set_array);
		}
	}
?>
	</form>
</div>

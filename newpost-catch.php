<?php
/*
Plugin Name: Newpost Catch
Plugin URI: http://www.imamura.biz/blog/newpost-catch/
Description: Thumbnails in new articles setting widget.
Version: 1.2.4
Author: Tetsuya Imamura
Text Domain: newpost-catch
Author URI: http://www.imamura.biz/blog/
License: GPL2
*/

//Include
include "class.php";

//Hook
add_action('widgets_init', create_function('', 'return register_widget("NewpostCatch");'));

//Instance
new NewpostCatch_SC();

//Hook npc_filter
add_action( 'admin_menu', 'npc_plugin_menu' );

//Add Admin Menu
function npc_plugin_menu() {
	add_options_page( 'Newpost-Catch', 'Newpost Catch', 'manage_options', "Newpost-Catch.php" , 'npc_options_page' );
}

//Define Option Page
function npc_options_page() {
	require('npc_admin.php');
}



/*  Copyright 2012-2014 Tetsuya Imamura (@s56bouya)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
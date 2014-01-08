<?php
/*
Plugin Name: WP Category Permalink
Plugin URI: http://www.meow.fr/wp-category-permalink
Description: Allows manual selection of a 'main' category for each post for better permalinks and SEO.
Version: 1.1.0
Author: Jordy Meow
Author URI: http://www.meow.fr
Remarks: This plugin was inspired by the Hikari Category Permalink. The way it works on the client-side is similar, and the JS file is actually the same one with a bit more code added to it.

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html

Originally developed for two of my websites: 
- Totoro Times (http://www.totorotimes.com) 
- Haikyo (http://www.haikyo.org)
*/

require( 'jordy_meow_footer.php' );

/**
 *
 * Posts list
 *
 */

add_filter( 'manage_posts_columns' , 'mwcp_manage_posts_columns' );
function mwcp_manage_posts_columns( $columns ) {
	$hidden_columns = get_user_option( "manageedit-postcolumnshidden" );
	if ( !in_array( 'scategory_permalink', (array) $hidden_columns) ) {
		$hidden_columns[] = 'scategory_permalink';
		$user = wp_get_current_user();
		update_user_option( $user->ID, "manageedit-postcolumnshidden", $hidden_columns );
	}
	return array_merge( $columns, array( 'scategory_permalink' => __( 'Permalink Category', 'wp-category-permalink' ) ) );
}

add_action( 'manage_posts_custom_column' , 'mwcp_custom_columns', 10, 2 );
function mwcp_custom_columns( $column, $post_id ) {
	if ( $column == 'scategory_permalink' ) {
		$cat_id = get_post_meta( $post_id , '_category_permalink', true ); 
		echo "<span class='scategory_permalink_name'>";
		if ( $cat_id != null ) {
			$cat = get_category( $cat_id );
			echo $cat->name;
		}
		else {
			$cat = get_the_category( $post_id );
			if ( count($cat) > 1 ) {
				echo '<span style="color: red;">' . $cat[0]->name . '</span>';
			}
			else if ( count($cat) == 1 ) {
				echo $cat[0]->name;
			}
		}
		echo "</span>";
	}
}

/**
 *
 * Post Edit CSS/JS + Update
 *
 */

add_action( 'admin_enqueue_scripts', 'mwcp_admin_enqueue_scripts' );

function mwcp_admin_enqueue_scripts () {
	wp_enqueue_script( 'wp-category-permalink.js', plugins_url('/wp-category-permalink.js', __FILE__), array( 'jquery' ), '1.0', false );
}

/**
 *
 * Post Edit CSS/JS + Update
 *
 */

add_action( 'admin_print_styles-post.php', 'mwcp_post_css' );
add_action( 'admin_print_styles-post-new.php','mwcp_post_css' );
add_action( 'admin_footer-post.php', 'mwcp_post_js' );
add_action( 'admin_footer-post-new.php', 'mwcp_post_js' );
add_action( 'transition_post_status', 'mwcp_transition_post_status', 0, 3 );

// Inject the CSS into the post edit UI
function mwcp_post_css() {
	echo "<style type=\"text/css\">.scategory_link{vertical-align:middle;display:none;cursor:pointer;cursor:hand}</style>\n";
}

// Inject the javascript into the post edit UI
function mwcp_post_js() {
	global $post;
	$categoryID = '';
	if ( $post->ID ) {
		$categoryID = get_post_meta( $post->ID, '_category_permalink', true );
	}
	echo "<script type=\"text/javascript\">jQuery('#categorydiv').sCategoryPermalink({current: '$categoryID'});</script>\n";
}

// Update the post meta
function mwcp_transition_post_status( $new_status, $old_status, $post ) {
	if ( !isset( $_POST['scategory_permalink'] ) )
		return;
	$scategory_permalink = $_POST['scategory_permalink'];
	if ( isset( $scategory_permalink ) ) {
		$cats = wp_get_post_categories( $post->ID );
		foreach( $cats as $cat ){
			if( $cat == $scategory_permalink ) {
				if ( !update_post_meta( $post->ID, '_category_permalink', $scategory_permalink ) ) {
					add_post_meta( $post->ID, '_category_permalink',  $scategory_permalink, true );
					return;
				}
			}
		}
	}
}

/**
 *
 * Update the category on-the-fly (reading-mode)
 *
 */

add_filter( 'post_link_category', 'mwcp_post_link_category', 10, 3 );

// Return the category set-up in '_category_permalink', otherwise return the default category
function mwcp_post_link_category( $cat, $cats, $post ) {

	$catmeta = get_post_meta($post->ID, '_category_permalink', true);
	//	$cat = get_category( $catmeta );
	foreach ( $cats as $cat ) {
		if ( $cat->term_id == $catmeta ) {
			return $cat;
		}
	}
	return $cat;
}

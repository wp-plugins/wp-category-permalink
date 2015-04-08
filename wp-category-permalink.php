<?php
/*
Plugin Name: WP Category Permalink
Plugin URI: http://www.meow.fr
Description: Allows manual selection of a 'main' category for each post for better permalinks and SEO. Pro version adds support for WooCommerce products.
Version: 1.7
Author: Jordy Meow, Yaniv Friedensohn
Author URI: http://www.meow.fr
Remarks: This plugin was inspired by the Hikari Category Permalink. The way it works on the client-side is similar, and the JS file is actually the same one with a bit more code added to it.

Originally developed for two of my websites: 
- Totoro Times (http://www.totorotimes.com) 
- Haikyo (http://www.haikyo.org)
*/

if ( is_admin() ) {
	require( 'jordy_meow_footer.php' );
	require( 'wpcp_settings.php' );
}

/**
 *
 * Posts list
 *
 */

add_filter( 'manage_posts_columns' , 'mwcp_manage_posts_columns' );
function mwcp_manage_posts_columns( $columns ) {
	global $post_type;
	
	if ($post_type == 'product' && wpcp_woocommerce_support() == false) {return $columns;}

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
	global $post_type;
	
	if ($post_type == 'product' && wpcp_woocommerce_support() == false) {return;}
	
	if ( $column == 'scategory_permalink' ) {
		$cat_id = get_post_meta( $post_id , '_category_permalink', true ); 
		echo "<span class='scategory_permalink_name'>";
		if ( $cat_id != null ) {
			$cat = get_category( $cat_id );
			if ( !isset( $cat ) ) {
				$terms = get_the_terms( $post_id, 'product_cat' );
				if ( empty( $terms ) ) {
					return $column;
				}
				echo $terms[$cat_id]->name;
			} else {
				echo $cat->name;
			}
		} 
		else {
			$cat = get_the_category( $post_id );
			if (empty($cat)) {
				$terms = get_the_terms( $post_id, 'product_cat' );
				if (empty( $terms ) ) { 
					return $column; 
				}
				$cat = array_values($terms);
			}
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
	global $post_type;
	
	if ($post_type == 'product' && wpcp_woocommerce_support() == false || wpcp_is_pro() == false) {return;}
	
	wp_enqueue_script( 'wp-category-permalink.js', plugins_url('/wp-category-permalink.js', __FILE__), array( 'jquery' ), '1.6', false );
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
	echo "<script type=\"text/javascript\">jQuery('.categorydiv').sCategoryPermalink({current: '$categoryID'});</script>\n";
}

// Update the post meta
function mwcp_transition_post_status( $new_status, $old_status, $post ) {
	if ( !isset( $_POST['scategory_permalink'] ) )
		return;
	$scategory_permalink = $_POST['scategory_permalink'];
	if ( isset( $scategory_permalink ) ) {
		$cats = wp_get_post_categories( $post->ID );
		
		if (empty($cats)) {
			update_post_meta( $post->ID, '_category_permalink', $scategory_permalink );
			return;
		}
		
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

add_filter( 'post_link', 'update_permalink', 10, 2 );
add_filter( 'post_type_link', 'update_permalink', 10, 2 );

function update_permalink( $url, $post ) {
	global $post_type;
	
	if ($post_type == 'product' && wpcp_woocommerce_support() == false) {return $url;}
	
	if (current_filter() == 'post_link') {
		
		$permalink_structure = get_option('permalink_structure');
		if (strpos($permalink_structure, '%category%') === false) {return $url;}
		
		$terms = get_the_category( $post->ID );
		if (empty($terms)) {return $url;}
		foreach ($terms as $term) {
			$cats[] = array('id' => $term->cat_ID, 'slug' => str_replace('%category%', $term->slug, $permalink_structure));
		}
	} else {
		
		$arr = get_option('woocommerce_permalinks');
		$permalink_structure = $arr['product_base'];
		if (strpos($permalink_structure, '%product_cat%') === false) {return $url;}
		
		$terms = get_the_terms( $post->ID, 'product_cat' );
		if ( empty( $terms ) ) {
			return $url;
		}
		foreach ( $terms as $term ) {
			$cats[] = array('id' => $term->term_id, 'slug' => str_replace( '%product_cat%', $term->slug, $permalink_structure ) );
		}
	}
	
	$category_permalink_id = (int) get_post_meta( $post->ID, '_category_permalink', true );
	
	foreach ( $cats as $cat ) {
		if ( $cat['id'] == $category_permalink_id ) {
			if (current_filter() == 'post_link') {
				if (strpos($cat['slug'], '%postname%') === false) {return $url;}
				return site_url(str_replace('%postname%', $post->post_name, $cat['slug']));
			} else {
				return site_url($cat['slug'].'/'.$post->post_name.'/');
			}
		}
	}
	
	return $url;
}

/**
 *
 * MENU ITEM (SETTINGS)
 *
 */

add_action( 'admin_menu', 'wpcp_admin_menu' );

function wpcp_admin_menu() {
	add_options_page( 'Category Permalink', 'Category Permalink', 'manage_options', 'wpcp_settings', 'wpcp_settings_page' );
}

/**
 *
 * PRO 
 *
 */

function wpcp_woocommerce_support() {
	return wpcp_getoption( "woocommerce", "wpcp_basics", false );
}

function wpcp_is_pro() {
	$validated = get_transient( 'wpcp_validated' );
	if ( $validated ) {
		$serial = get_option( 'wpcp_pro_serial');
		return !empty( $serial );
	}
	$subscr_id = get_option( 'wpcp_pro_serial', "" );
	if ( !empty( $subscr_id ) )
		return wpcp_validate_pro( wpcp_getoption( "subscr_id", "wpcp_pro", array() ) );
	return false;
}

function wpcp_validate_pro( $subscr_id ) {
	if ( empty( $subscr_id ) ) {
		delete_option( 'wpcp_pro_serial', "" );
		delete_option( 'wpcp_pro_status', "" );
		set_transient( 'wpcp_validated', false, 0 );
		return false;
	}
	require_once ABSPATH . WPINC . '/class-IXR.php';
	require_once ABSPATH . WPINC . '/class-wp-http-ixr-client.php';
	$client = new WP_HTTP_IXR_Client( 'http://apps.meow.fr/xmlrpc.php' );
	if ( !$client->query( 'meow_sales.auth', $subscr_id, 'category-permalink', get_site_url() ) ) { 
		update_option( 'wpcp_pro_serial', "" );
		update_option( 'wpcp_pro_status', "A network error: " . $client->getErrorMessage() );
		set_transient( 'wpcp_validated', false, 0 );
		return false;
	}
	$post = $client->getResponse();
	if ( !$post['success'] ) {
		if ( $post['message_code'] == "NO_SUBSCRIPTION" ) {
			$status = __( "Your serial does not seem right." );
		}
		else if ( $post['message_code'] == "NOT_ACTIVE" ) {
			$status = __( "Your subscription is not active." );
		}
		else if ( $post['message_code'] == "TOO_MANY_URLS" ) {
			$status = __( "Too many URLs are linked to your subscription." );
		}
		else {
			$status = "There is a problem with your subscription.";
		}
		update_option( 'wpcp_pro_serial', "" );
		update_option( 'wpcp_pro_status', $status );
		set_transient( 'wpcp_validated', false, 0 );
		return false;
	}
	set_transient( 'wpcp_validated', $subscr_id, 3600 * 24 * 100 );
	update_option( 'wpcp_pro_serial', $subscr_id );
	update_option( 'wpcp_pro_status', __( "Your subscription is enabled." ) );
	return true;
}
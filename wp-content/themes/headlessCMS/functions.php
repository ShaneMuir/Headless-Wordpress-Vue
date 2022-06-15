<?php

add_action('rest_api_init', function() {
	register_rest_route('markers/v1', 'post', array(
		'methods' => WP_REST_SERVER::READABLE,
		'callback' => 'markers_endpoint_data',
		'permission_callback' => '__return_true'
	));
});

function markers_endpoint_data( $data ) {
	$posts = get_posts($args = array(
		'post_type' => 'post',
		'posts_per_page'=>-1,
		'numberposts'=>-1
	));

	foreach ($posts as $post) {
		$post->acf = get_fields($post->ID);
	}
	return  $posts;
}

/*
 *  Removes the WordPress Logo From Admin
 */
function remove_wp_logo_from_admin() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'wp-logo' );
}
add_action( 'wp_before_admin_bar_render', 'remove_wp_logo_from_admin', 0 );

/*
 *  Removes the WordPress footer and add a custom one
 */
function remove_footer_admin() {
	?> &copy; <?php echo date('Y') . ' <a class="site-info" href="'.home_url().'">'.get_bloginfo('name').'</a>';
}

add_filter('admin_footer_text', 'remove_footer_admin');

/*
 *  Remove the WordPress Version from the admin footer
 */
function remove_wordpress_version_from_footer() {
	remove_filter( 'update_footer', 'core_update_footer' );
}

add_action( 'admin_menu', 'remove_wordpress_version_from_footer' );

/*
 * Complete remove comment support from post types
 */

// First, this will disable support for comments and trackbacks in post types
function headless_disable_comments_post_types_support() {
	$post_types = get_post_types();
	foreach ( $post_types as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}

add_action( 'admin_init', 'headless_disable_comments_post_types_support' );

// Then close any comments open comments on the front-end just in case
function headless_disable_comments_status() {
	return false;
}

add_filter( 'comments_open', 'headless_disable_comments_status', 20, 2 );
add_filter( 'pings_open', 'headless_disable_comments_status', 20, 2 );

// Finally, hide any existing comments that are on the site.
function headless_disable_comments_hide_existing_comments( $comments ) {
	$comments = array();

	return $comments;
}

add_filter( 'comments_array', 'headless_disable_comments_hide_existing_comments', 10, 2 );

// Removes from admin menu
function headless_remove_admin_menus() {
	remove_menu_page( 'edit-comments.php' );
}

add_action( 'admin_menu', 'headless_remove_admin_menus' );

// Removes from admin bar
function headless_admin_bar_render() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('comments');
}

add_action( 'wp_before_admin_bar_render', 'headless_admin_bar_render' );

// Redirect any user trying to access comments page
function headless_custom_disable_comments_admin_menu_redirect() {
	global $pagenow;
	if ($pagenow === 'edit-comments.php') {
		wp_redirect(admin_url());
        exit;
	}
}

add_action('admin_init', 'headless_custom_disable_comments_admin_menu_redirect');

	function headless_unregister_post_type() {
		global $wp_post_types;
		if ( isset( $wp_post_types['page'] ) ) {
			unset( $wp_post_types['page'] );

			return true;
		}

		return false;
	}

add_action('init', 'headless_unregister_post_type');

// Remove side menu
add_action( 'admin_menu', 'remove_default_post_type', 11 );

function remove_default_post_type() {
	remove_menu_page( 'edit.php?post_type=page');
}
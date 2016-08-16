<? defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/*
Plugin Name: Ajax filter demo for wp-support.pro
Plugin URI: http://demo.wp-support.pro/advisor/
Description: Демонстрирует возможности фильтра постов по мета-записям на Ajax
Version: 1
Author: Eugene Holin
Author URI: http://holin.biz/
License: GPL3
Copyright: 2016
*/

$plugin_dir_path = plugin_dir_path( __FILE__ );
$plugin_dir_url = plugin_dir_url( __FILE__ );

include_once $plugin_dir_path . 'product-metaboxes.php';

/* Load plugin textdomain */
add_action( 'plugins_loaded', 'wpsfa_load_textdomain' );
function wpsfa_load_textdomain() {
	load_plugin_textdomain( 'wpsfa', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/* load frontend js and css styles */
add_action( 'wp_enqueue_scripts', 'wpsfa_register_frontend_scripts' );
function wpsfa_register_frontend_scripts() {
	global $plugin_dir_url;
	wp_register_style( 'wpsfa-frontend-css', $plugin_dir_url . '/frontend.css' );
	wp_enqueue_style( 'wpsfa-frontend-css' );

	wp_register_style( 'icheck-flat', $plugin_dir_url . '/vendor/icheck/skins/flat/blue.css' );
	wp_enqueue_style( 'icheck-flat' );

	wp_register_script( 'icheck', $plugin_dir_url . '/vendor/icheck/icheck.min.js', array( 'jquery' ), FALSE, TRUE );
	wp_enqueue_script( 'icheck' );

	wp_register_script( 'wpsfa-frontend', $plugin_dir_url . '/frontend.js', array( 'jquery' ), FALSE, TRUE );
	wp_localize_script( 'wpsfa-frontend', 'wpsfaAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'wpsfa-frontend' );
}

/* register custom post type and flush rewrite rules for custom post type url */
register_activation_hook( __FILE__, 'wpsfa_flush_rewrites' );
function wpsfa_flush_rewrites() {
	flush_rewrite_rules();
}

/* flush rewrite rules after plugin deactivating */
register_deactivation_hook( __FILE__, 'wpsfa_flush_rewrites_deactivate' );
function wpsfa_flush_rewrites_deactivate() {
	flush_rewrite_rules();
}

/**
 * declare custom post type for demo - product
 */
add_action( 'init', 'wpsfa_custom_post_product' );
function wpsfa_custom_post_product() {
	$labels = array(
		'name' => _x( 'Product', 'post type general name', 'wpsfa' ),
		'singular_name' => _x( 'Product', 'post type singular name', 'wpsfa' ),
		'add_new' => _x( 'Add New', 'product', 'wpsfa' ),
		'add_new_item' => __( 'Add New Product', 'wpsfa' ),
		'edit_item' => __( 'Edit Product', 'wpsfa' ),
		'new_item' => __( 'New Product', 'wpsfa' ),
		'all_items' => __( 'All Products', 'wpsfa' ),
		'view_item' => __( 'View Product', 'wpsfa' ),
		'search_items' => __( 'Search Products', 'wpsfa' ),
		'not_found' => __( 'No Products found', 'wpsfa' ),
		'not_found_in_trash' => __( 'No Products found in the Trash', 'wpsfa' ),
		'parent_item_colon' => '',
		'menu_name' => __( 'Products', 'wpsfa' ),
	);
	$args = array(
		'labels' => $labels,
		'description' => __( 'Create a product and use them in a list of products', 'wpsfa' ),
		'menu_icon' => 'dashicons-tickets-alt',
		'public' => TRUE,
		'show_in_nav_menus' => FALSE,
		'exclude_from_search' => TRUE,
		'supports' => array( 'title', 'editor', 'page-attributes', 'thumbnail', 'author', 'comments' ),
		'has_archive' => FALSE,
	);
	register_post_type( 'demo-product', $args );
}

/** -------------------------------------------------------------------------------------------------
 * Create custom taxonomy - account currency
 * Name: demo-currency
 * --------------------------------------------------------------------------------------------------
 */
add_action( 'init', 'wpsfa_taxonomy_account_currency' );
function wpsfa_taxonomy_account_currency() {
	// create a new taxonomy
	register_taxonomy( 'demo-currency', 'demo-product', array(
		'label' => __( 'Account currency', 'wpsfa' ),
		'rewrite' => array( 'slug' => 'account-currency' ),
		'show_in_nav_menus' => FALSE,
		'exclude_from_search' => TRUE,
		'capabilities' => array(
			'manage_terms' => 'manage_options', //by default only admin
			'edit_terms' => 'manage_options',
			'delete_terms' => 'manage_options',
			'assign_terms' => 'edit_posts'  // means administrator', 'editor', 'author', 'contributor'
		),
	) );
}

add_action( 'init', 'wpsfa_advisor_rewrite' );
function wpsfa_advisor_rewrite() {
	global $wp_rewrite;

	add_rewrite_tag( '%advisor%', '([^&]+)' );
	add_rewrite_rule( '^advisor/?', 'index.php?pagetype=advisor', 'top' );
	add_rewrite_endpoint( 'pagetype', EP_PERMALINK | EP_PAGES );

	//flush rules to get this to work properly (do this once, then comment out)
	//$wp_rewrite->flush_rules();
}

add_action( 'template_redirect', 'wpsfa_advisor_redirect' );
function wpsfa_advisor_redirect() {
	global $wp, $plugin_dir_path;

	$template = $wp->query_vars;

	if ( array_key_exists( 'pagetype', $template ) AND $template['pagetype'] == 'advisor' ) {

		include $plugin_dir_path . '/templates/advisor-page.php';
		exit;
	}
}

add_filter( 'pre_get_document_title', 'wpsfa_advisor_title', 100 );
function wpsfa_advisor_title( $title ) {
	global $wp_query;

	$template = $wp_query->query_vars;

	if ( array_key_exists( 'pagetype', $template ) AND $template['pagetype'] == 'advisor' ) {
		$title = __( 'Help in choosing a product', 'wpsfa' );
	}

	return $title;
}

add_filter( 'body_class', 'wpsfa_advisor_body_class' );
function wpsfa_advisor_body_class( $classes ) {
	global $wp_query;

	$template = $wp_query->query_vars;

	if ( array_key_exists( 'pagetype', $template ) AND $template['pagetype'] == 'advisor' ) {
		$classes[] = 'advisor-page';
	}

	return $classes;
}

/**
 * Ajax for advisor
 */
add_action( 'wp_ajax_do_product_filter', 'wpsfa_do_product_filter' );
add_action( 'wp_ajax_nopriv_do_product_filter', 'wpsfa_do_product_filter' );
function wpsfa_do_product_filter() {
	check_ajax_referer( 'wpsfa_do_product_filter', 'wpsfa_product_filter_form' );

	$args = wpsfa_build_product_filter_args();
	$filter_results = wpsfa_display_products( $args );

	wp_send_json_success( array(
		'html' => $filter_results,
	) );
}

function wpsfa_build_product_filter_args() {
	$args = array(
		'post_type' => 'demo-product',
		'posts_per_page' => - 1,
		'offset' => 0,
		'order' => 'DESC',
		'orderby' => 'ID',
		'post_status' => 'publish',
		'ignore_sticky_posts' => TRUE,
	);

	$args['meta_query']['relation'] = 'AND';

	foreach ( $_POST as $key => $value ) {
		if ( $value ) {
			switch ( $key ) {
				case 'wpsfa_product_type':

					$args['meta_query'][] = array(
						'key' => '_wpsfa_product_type',
						'value' => $value,
						'compare' => '=',
					);
					break;
				case 'wpsfa_product_profit':
					$args['meta_query'][] = array(
						'key' => '_wpsfa_product_profit',
						'value' => $value,
						'compare' => '=',
					);
					break;
				case 'wpsfa_product_cashback':
					$args['meta_query'][] = array(
						'key' => '_wpsfa_product_cashback',
						'value' => $value,
						'compare' => '=',
					);
					break;
			}
		}
	}

	if ( isset( $_POST['wpsfa_product_currency'] ) ) {
		$product_currencies = $_POST['wpsfa_product_currency'];
		if ( $product_currencies != '' ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'demo-currency',
					'field' => 'term_id',
					'terms' => $product_currencies,
				),
			);
		}
	}

	return $args;
}

function wpsfa_display_products( $args ) {
	$products = new WP_Query( $args );
	if ( $products->have_posts() ) {
		$output = '<div class="wpsfa-products-wrapper">';
		while ( $products->have_posts() ){
			$products->the_post();
			$post_id = get_the_ID();
			$title = get_the_title();

			$product_type = get_post_meta( $post_id, '_wpsfa_product_type', TRUE );
			$product_profit = get_post_meta( $post_id, '_wpsfa_product_profit', TRUE );
			$product_cashback = get_post_meta( $post_id, '_wpsfa_product_cashback', TRUE );

			$product_type = ( $product_type == 'credit' ) ? __( 'credit', 'wpsfa' ) : __( 'debit', 'wpsfa' );
			$product_profit = ( $product_profit == 'yes' ) ? __( 'yes', 'wpsfa' ) : __( 'no', 'wpsfa' );
			$product_cashback = ( $product_cashback == 'yes' ) ? __( 'yes', 'wpsfa' ) : __( 'no', 'wpsfa' );

			if ( has_post_thumbnail() ) {
				$post_thumbnail_id = get_post_thumbnail_id();
				$thumbnail = wp_get_attachment_image_src( $post_thumbnail_id, 'medium' );
				$thumbnail_image = '<img src="' . $thumbnail[0] . '" title="' . $title . '">';
			} else {
				$thumbnail_image = '';
			}

			$terms = wp_get_post_terms( $post_id, 'demo-currency', array( "fields" => "all" ) );
			$terms_output = '';
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$terms_output .= '<span class="wpsfa-product-currency">' . $term->name . '</span>';
				}
			}

			$output .= '<div class="wpsfa-products-row">';
			$output .= '<div class="wpsfa-product-image">' . $thumbnail_image . '</div>';
			$output .= '<div class="wpsfa-product-descr">';
			$output .= '<div class="wpsfa-product-title">' . $title . '</div>';
			$output .= '<div class="wpsfa-product-meta">' . __( 'Card type', 'wpsfa' ) . ': ' . $product_type . '</div>';
			$output .= '<div class="wpsfa-product-meta">' . __( 'Interest on the balance', 'wpsfa' ) . ': ' . $product_profit . '</div>';
			$output .= '<div class="wpsfa-product-meta">' . __( 'Cashback', 'wpsfa' ) . ': ' . $product_cashback . '</div>';
			$output .= '<div class="wpsfa-product-meta">' . __( 'Account currency', 'wpsfa' ) . ': ' . $terms_output . '</div>';
			$output .= '</div>'; // .wpsfa-product-descr
			$output .= '</div>'; // .wpsfa-products-row
		}
		$output .= '</div>';
	}

	return $output;
}

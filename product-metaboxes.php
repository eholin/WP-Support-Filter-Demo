<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/* ########################################################
 *
 * Meta boxes for products with post save handler
 *
 * ######################################################## */

/** -------------------------------------------------------------------------------------------------
 * Adds a meta box with options
 * --------------------------------------------------------------------------------------------------
 */
add_action( 'add_meta_boxes', 'wpsfa_product_options' );
function wpsfa_product_options() {
	add_meta_box( 'wpsfa_product_options', __( 'Product options', 'wpsfa' ), 'wpsfa_product_options_box_content', 'demo-product', 'normal', 'default' );
}

function wpsfa_product_options_box_content( $post ) {

	wp_nonce_field( 'wpsfa_product_options_save', 'wpsfa_product_options_box_content_nonce' );

	$product_type = get_post_meta( $post->ID, '_wpsfa_product_type', TRUE );
	$product_profit = get_post_meta( $post->ID, '_wpsfa_product_profit', TRUE );
	$product_cashback = get_post_meta( $post->ID, '_wpsfa_product_cashback', TRUE );

	$output = '<div>';
	$output .= '<p>';
	$output .= '<label for="wpsfa_product_type">' . __( 'Card type', 'wpsfa' ) . ':</label> ';
	$output .= '<select name="wpsfa_product_type" id="wpsfa_product_type">';
	$output .= '<option value="debit" ' . selected( 'debit', $product_type, FALSE ) . '>' . __( 'Debit', 'wpsfa' ) . '</option>';
	$output .= '<option value="credit" ' . selected( 'credit', $product_type, FALSE ) . '>' . __( 'Credit', 'wpsfa' ) . '</option>';
	$output .= '</select>';
	$output .= '</p>';

	$output .= '<p>';
	$output .= '<label for="wpsfa_product_profit">' . __( 'Interest on the balance', 'wpsfa' ) . ':</label> ';
	$output .= '<select name="wpsfa_product_profit" id="wpsfa_product_profit">';
	$output .= '<option value="yes" ' . selected( 'yes', $product_profit, FALSE ) . '>' . __( 'Yes', 'wpsfa' ) . '</option>';
	$output .= '<option value="" ' . selected( '', $product_profit, FALSE ) . '>' . __( 'No matter', 'wpsfa' ) . '</option>';
	$output .= '</select>';
	$output .= '</p>';

	$output .= '<p>';
	$output .= '<label for="wpsfa_product_cashback">' . __( 'Cashback', 'wpsfa' ) . ':</label> ';
	$output .= '<select name="wpsfa_product_cashback" id="wpsfa_product_cashback">';
	$output .= '<option value="yes" ' . selected( 'yes', $product_cashback, FALSE ) . '>' . __( 'Yes', 'wpsfa' ) . '</option>';
	$output .= '<option value="" ' . selected( '', $product_cashback, FALSE ) . '>' . __( 'No matter', 'wpsfa' ) . '</option>';
	$output .= '</select>';
	$output .= '</p>';

	$output .= '</div>';

	echo $output;
}

/** -------------------------------------------------------------------------------------------------
 * Save data from a custom meta boxes for post
 * --------------------------------------------------------------------------------------------------
 */

add_action( 'save_post', 'wpsfa_product_options_meta_box_save' );
function wpsfa_product_options_meta_box_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) AND DOING_AUTOSAVE ) {
		return;
	}

	if ( ! $_POST AND $_POST['post_type'] != 'demo-product' ) {
		return;
	}

	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	if ( wp_verify_nonce( $_POST['wpsfa_product_options_box_content_nonce'], 'wpsfa_product_options_save' ) ) {

		if ( ! empty( $_POST['wpsfa_product_type'] ) ) {
			$product_type = sanitize_text_field( $_POST['wpsfa_product_type'] );
		} else {
			$product_type = '';
		}
		update_post_meta( $post_id, '_wpsfa_product_type', $product_type );

		if ( ! empty( $_POST['wpsfa_product_profit'] ) ) {
			$product_profit = sanitize_text_field( $_POST['wpsfa_product_profit'] );
		} else {
			$product_profit = '';
		}
		update_post_meta( $post_id, '_wpsfa_product_profit', $product_profit );

		if ( ! empty( $_POST['wpsfa_product_cashback'] ) ) {
			$product_cashback = sanitize_text_field( $_POST['wpsfa_product_cashback'] );
		} else {
			$product_cashback = '';
		}
		update_post_meta( $post_id, '_wpsfa_product_cashback', $product_cashback );
	}
}

<?php
/**
 * The template for advisor page.
 * Work only with rewrite rules.
 *
 */

get_header(); ?>

	<div id="full-width" class="content-area">
		<main id="main" class="site-main advisor-container container" role="main">
			<div class="advisor-form-wrapper">
				<form action="<?php echo home_url( '/advisor/' ); ?>" method="post" id="advisor-form">
					<input type="hidden" name="action" value="do_product_filter"/>
					<?php wp_nonce_field( 'wpsfa_do_product_filter', 'wpsfa_product_filter_form', FALSE, TRUE ) ?>

					<div class="wpsfa-advisor-form-section">
						<h3><?php _e( 'Product options', 'wpsfa' ); ?>:</h3>

						<div class="wpsfa-advisor-form-item">
							<label class="wpsfa-advisor-checkbox-label" for="wpsfa_product_type"><?php _e( 'Card type', 'wpsfa' ); ?>:</label>
							<div class="wpsfa-advisor-radiobutton"><input name="wpsfa_product_type" id="wpsfa_product_type" type="radio" value="" class="wpsfa-icheck" checked> <?php _e( 'No matter', 'wpsfa' ); ?></div>
							<div class="wpsfa-advisor-radiobutton"><input name="wpsfa_product_type" id="wpsfa_product_type" type="radio" value="debit" class="wpsfa-icheck"> <?php _e( 'Debit', 'wpsfa' ); ?></div>
							<div class="wpsfa-advisor-radiobutton"><input name="wpsfa_product_type" id="wpsfa_product_type" type="radio" value="credit" class="wpsfa-icheck"> <?php _e( 'Credit', 'wpsfa' ); ?></div>
						</div>

						<div class="wpsfa-advisor-form-item">
							<label class="wpsfa-advisor-checkbox-label" for="wpsfa_product_profit"><?php _e( 'Interest on the balance', 'wpsfa' ) ?>:
								<input name="wpsfa_product_profit" id="wpsfa_product_profit" type="checkbox" value="yes" class="wpsfa-icheck">
							</label>
						</div>

						<div class="wpsfa-advisor-form-item">
							<label class="wpsfa-advisor-checkbox-label" for="wpsfa_product_cashback"><?php _e( 'Cashback', 'wpsfa' ) ?>:
								<input name="wpsfa_product_cashback" id="wpsfa_product_cashback" type="checkbox" value="yes" class="wpsfa-icheck">
							</label>
						</div>

					</div>

					<div class="wpsfa-advisor-form-section">
						<h3><?php _e( 'Account currency', 'wpsfa' ); ?>:</h3>
						<div class="wpsfa-advisor-form-item">
							<?php
							$terms = get_terms( array(
								'taxonomy' => 'demo-currency',
								'orderby' => 'count',
								'order' => 'DESC',
								'hide_empty' => TRUE,
							) );
							?>
								<div class="wpsfa-advisor-radiobutton">
									<label class="wpsfa-advisor-checkbox-label" for="wpsfa_product_currency"><?php _e( 'No matter', 'wpsfa' ); ?></label>
									<input name="wpsfa_product_currency" id="wpsfa_product_currency" type="radio" value="<?php echo $term->ID; ?>" class="wpsfa-icheck" checked>
								</div>
							<?php

							foreach ( $terms as $term ) {
								?>
								<div class="wpsfa-advisor-radiobutton">
									<label class="wpsfa-advisor-checkbox-label" for="wpsfa_product_currency"><?php echo $term->name; ?></label>
									<input name="wpsfa_product_currency" id="wpsfa_product_currency" type="radio" value="<?php echo $term->term_id; ?>" class="wpsfa-icheck">
								</div>

								<?php
							}

							?>
						</div>
					</div>
				</form>
			</div>
			<div class="advisor-products-list">
				<?php

				$args = array(
					'post_type' => 'demo-product',
					'posts_per_page' => - 1,
					'offset' => 0,
					'order' => 'ASC',
					'orderby' => 'ID',
					'post_status' => 'publish',
					'ignore_sticky_posts' => TRUE,
				);

				echo wpsfa_display_products( $args );

				?>
			</div>

		</main>
		<!-- #main -->
	</div><!-- #primary -->

<?php
//get_sidebar();
get_footer();

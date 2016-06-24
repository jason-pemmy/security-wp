		</main>
		<!--<footer class="page-footer">
			<div class="container">
				<nav class="nav-footer">
				</nav>
			</div>
			<div class="container">
				<div class="footer-copyright">
					&copy; <?php echo date( 'Y' ) ?>
					<strong>
						<?php echo function_exists('the_field')?get_field( 'company_name', 'option' ):''; ?>.
					</strong>
					<a href="<?php echo home_url();?>/terms-conditions">
						<?php _e('Terms & Conditions', 'the-theme');?>
					</a>
					<a href="<?php echo home_url();?>/privacy-policy">
						<?php _e('Privacy Policy', 'the-theme');?>
					</a>

					<div class="credit">
						<?php do_action('add_credit_line_to_footer'); ?>
					</div>
				</div>
			</div>
		</footer>-->
		<?php //wp_footer(); ?>
	</body>
</html>

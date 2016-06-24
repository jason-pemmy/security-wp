<a href="<?php echo TBK_Careers::careers_url();?>">
	<?php if( ! empty( $careers->post_count)) { ?>
		<span class="badge">
			<?php echo $careers->post_count;?>
		</span>
	<?php } ?>
	<?php echo apply_filters('careers_available_text',
		($careers->post_count == 1?__('Career', CAREER_DOMAIN):__('Careers', CAREER_DOMAIN)).
		( ! empty( $careers->post_count)?' '.__('Open', TEXT_DOMAIN):''));?>
</a>
<?php global $slider_class;?>
<div class="slider <?php echo $slider_class;?>" id="main-banner">
    <div class="slides_container">
        <?php if (have_posts()) while (have_posts()) : the_post(); ?>
            <div class="slide">
                <?php if(has_post_thumbnail())
                    the_post_thumbnail('full', array('title' => ''));?>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<script type="text/javascript">
jQuery(function($){
    $("#main-banner").slides({<?php echo setup_slider_settings();?>});
});
</script>
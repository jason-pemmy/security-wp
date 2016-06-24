<?php $setttings = get_slider_settings(); ?>
<div class="wrap">
    <div id="icon-options-general" class="icon32"></div>
    <h2>Slider Settings</h2>
    <p></p>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <table class="widefat">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Value</th>
                    <th>Description</th>
                </tr>
            </thead>
            <?php foreach(_slider_setting_vars() as $vars) { ?>
                <tr>
                    <td style="width: 20%;">
                        <?php echo $vars['text'];?>
                    </td>
                    <td>
                        <input type="<?php echo $vars['type'];?>" <?php echo (($vars['type']=='checkbox'&&$settings[$vars['name']]==$vars['value'])?'checked="checked"':'');?>
                        value="<?php echo (isset($settings[$vars['name']])?$settings[$vars['name']]:$vars['value']);?>" name="<?php echo $vars['name'];?>"/>
                    </td>
                    <td><?php echo (isset($vars['descr'])?$vars['descr']:'');?></td>
                </tr>
            <?php } ?>
        </table>
        <p>
            <input class="button-primary" type="submit" name="save_slider_settings" value="Save Settings" />
        </p>
    </form>
    <div class="description">
        <p>
            This slider uses Slides JS. <br />
            Read Documentation here: <a href="http://www.slidesjs.com/#docs" target="_blank">http://www.slidesjs.com/#docs</a>
        </p>
    </div>
</div>
<fieldset class ="<?php echo $this->module_enabled($key) ? ' enabled' : '' ?>">
    <h3>
        <input class ="enable-module" id ="<?php echo $key ?>" type ="checkbox" <?php checked(true,$this->module_enabled($key)); ?> name ="<?php echo $key ?>" />
        <label for ="<?php echo $key ?>"><?php echo $module->title; ?></label>
    </h3>
	<?php if (isset($this->message) and $this->message) : ?>
		<div class="module-message">
			<?php echo $this->message; ?>
			<?php if (isset($this->confirm)) : ?>
				<div id="module-confirm-<?php echo $this->confirm_action; ?>" class="module-confirm"><?php echo $this->confirm; ?> <a href="#" class="yes">Yes</a> <a href="#" class="no">No</a></div>
			<?php endif; ?>
		</div>
	<?php else : ?>
	    <a title ="<?php echo $module->title ?>" class ="description item thickbox" href ="#TB_inline?width=600&height=550&inlineId=<?php echo $key ?>_id">Description</a>
	    <div id ="<?php echo $key ?>_id" class ="module-description">
	       <?php echo $module->description; ?>
	    </div>
	    <?php

	    if ($this->module_enabled($key)) {
			switch(true) : 
			case method_exists($instance = $this->get_module($key), 'form_options') : tbk_toolbox::get_settings($key); // this will save settings $_POSTed . ?>
		        <a title ="<?php echo $module->title ?>" class ="settings item thickbox" href ="#TB_inline?width=600&height=550&inlineId=<?php echo $key ?>_settings">Settings</a>
		        <div id ="<?php echo $key ?>_settings" class ="module-description">
		            <?php $instance->form_options(); ?>
		            <div style ="clear:both"></div>
		        </div>
	        <?php
				break;
			case (isset($instance) and isset($instance->settings_page)) : ?>
	        	<a title ="<?php echo $module->title; ?>" class="settings item" href="<?php echo $instance->settings_page; ?>">Settings</a>
			<?php 
				break;
			endswitch;
		} 
	
	    ?>
	<?php endif; ?>
    <div style ="clear:both"></div>
</fieldset>
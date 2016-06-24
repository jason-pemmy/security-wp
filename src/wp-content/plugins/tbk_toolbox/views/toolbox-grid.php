<?php
// Form Markup
?>

<div id ="toolbox-grid" class ="wrap">
    <h2>tbk Tools</h2>
    <div id ="tbkcp_primary_form" class="gf-form">
         <ul class="gf-checkbox"> <?php

            //Loop through all the classes in the /cls folder. Set checkboxes with appropriate meta data
            foreach ($this->registered_modules as $key => $module) : ?>
                <li><?php include('toolbox-grid-cell.php'); ?></li>
            <?php endforeach; ?>
		</ul>

    </div>
</div>

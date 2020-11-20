<div class="wrap">
    <h1>Dashboard</h1>
	<?php settings_errors(); ?>

    <form method="post" action="options.php">
            <?php
            settings_fields('2eleTheme');
            do_settings_sections('2eleTheme');
            submit_button();
            ?>
    </form>
</div>
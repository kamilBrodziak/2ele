<div class="wrap">
    <h1>Dashboard</h1>
	<?php settings_errors(); ?>

    <form method="post" action="options.php">
            <?php
            settings_fields('kBPlugin');
            do_settings_sections('kBPlugin');
            submit_button();
            ?>
    </form>
</div>